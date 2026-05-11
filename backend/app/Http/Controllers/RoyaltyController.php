<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Royalty;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoyaltyController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdminOrOperator($request);

        $query = Royalty::query()->with(['book.category', 'author', 'creator:id,name']);
        $this->applyFilters($query, $request);

        return $query->latest('created_at')->paginate($request->input('per_page', 10));
    }

    public function summary(Request $request)
    {
        $this->authorizeAdminOrOperator($request);

        $query = Royalty::query();
        $this->applyFilters($query, $request);

        return response()->json($this->summaryFromQuery($query));
    }

    public function store(Request $request)
    {
        $this->authorizeAdminOrOperator($request);

        $data = $this->validatedData($request);
        $book = Book::with('author')->findOrFail($data['book_id']);
        $this->validatePublishedBook($book);

        $data['author_id'] = $book->author_id;
        $data = $this->calculateAmounts($data);
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;
        $data = $this->normalizePaidAt($data);

        $royalty = Royalty::create($data);

        return response()->json($royalty->load(['book.category', 'author', 'creator:id,name']), 201);
    }

    public function show(Request $request, Royalty $royalty)
    {
        $this->authorizeAdminOrOperator($request);

        return response()->json($royalty->load(['book.category', 'author', 'creator:id,name', 'updater:id,name']));
    }

    public function update(Request $request, Royalty $royalty)
    {
        $this->authorizeAdminOrOperator($request);

        $data = $this->validatedData($request);
        $book = Book::with('author')->findOrFail($data['book_id']);
        $this->validatePublishedBook($book);

        $data['author_id'] = $book->author_id;
        $data = $this->calculateAmounts($data);
        $data['updated_by'] = $request->user()->id;
        $data = $this->normalizePaidAt($data, $royalty);

        $royalty->update($data);

        return response()->json($royalty->fresh()->load(['book.category', 'author', 'creator:id,name', 'updater:id,name']));
    }

    public function destroy(Request $request, Royalty $royalty)
    {
        $this->authorizeAdminOrOperator($request);

        if ($royalty->status !== 'draft') {
            return response()->json(['message' => 'Hanya royalti draft yang dapat dihapus.'], 422);
        }

        $royalty->delete();

        return response()->json(['message' => 'Royalti berhasil dihapus.']);
    }

    public function authorIndex(Request $request)
    {
        $this->authorizeAuthor($request);

        $author = $request->user()->author;
        abort_if(!$author, 404, 'Profil author belum tersedia.');

        $query = Royalty::query()
            ->where('author_id', $author->id)
            ->with(['book.category', 'author']);

        $this->applyFilters($query, $request, false);

        return $query->latest('created_at')->paginate($request->input('per_page', 10));
    }

    public function authorSummary(Request $request)
    {
        $this->authorizeAuthor($request);

        $author = $request->user()->author;
        abort_if(!$author, 404, 'Profil author belum tersedia.');

        $query = Royalty::query()->where('author_id', $author->id);
        $this->applyFilters($query, $request, false);

        return response()->json($this->summaryFromQuery($query));
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'book_id' => 'required|exists:books,id',
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2000|max:2100',
            'sold_qty' => 'required|integer|min:0',
            'sale_price_per_unit' => 'required|numeric|min:0',
            'royalty_per_unit' => 'required|numeric|min:0',
            'status' => ['required', Rule::in(['draft', 'pending', 'paid'])],
            'paid_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
    }

    private function calculateAmounts(array $data): array
    {
        $soldQty = (int) $data['sold_qty'];
        $salePrice = (float) $data['sale_price_per_unit'];
        $royaltyPerUnit = (float) $data['royalty_per_unit'];

        $data['gross_amount'] = round($soldQty * $salePrice, 2);
        $data['royalty_amount'] = round($soldQty * $royaltyPerUnit, 2);

        return $data;
    }

    private function normalizePaidAt(array $data, ?Royalty $royalty = null): array
    {
        if ($data['status'] === 'paid' && empty($data['paid_at'])) {
            $data['paid_at'] = $royalty?->paid_at ?: now();
        }

        if ($data['status'] !== 'paid') {
            $data['paid_at'] = null;
        }

        return $data;
    }

    private function validatePublishedBook(Book $book): void
    {
        abort_if($book->status !== 'published', 422, 'Royalti hanya dapat dibuat untuk buku published.');
    }

    private function applyFilters($query, Request $request, bool $allowAuthorFilter = true): void
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q
                ->whereHas('book', fn ($book) => $book
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%"))
                ->orWhereHas('author', fn ($author) => $author->where('name', 'like', "%{$search}%")));
        }

        if ($request->filled('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        if ($allowAuthorFilter && $request->filled('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('period_month')) {
            $query->where('period_month', $request->period_month);
        }

        if ($request->filled('period_year')) {
            $query->where('period_year', $request->period_year);
        }
    }

    private function summaryFromQuery($query): array
    {
        $base = clone $query;

        return [
            'total_entries' => (clone $base)->count(),
            'total_books' => (clone $base)->distinct('book_id')->count('book_id'),
            'total_sold_qty' => (int) (clone $base)->sum('sold_qty'),
            'gross_amount' => (float) (clone $base)->sum('gross_amount'),
            'royalty_amount' => (float) (clone $base)->sum('royalty_amount'),
            'draft_amount' => (float) (clone $base)->where('status', 'draft')->sum('royalty_amount'),
            'pending_amount' => (float) (clone $base)->where('status', 'pending')->sum('royalty_amount'),
            'paid_amount' => (float) (clone $base)->where('status', 'paid')->sum('royalty_amount'),
        ];
    }

    private function authorizeAdminOrOperator(Request $request): void
    {
        abort_if(!in_array($request->user()->role?->name, ['Admin', 'Operator'], true), 403);
    }

    private function authorizeAuthor(Request $request): void
    {
        abort_if($request->user()->role?->name !== 'Author', 403);
    }
}
