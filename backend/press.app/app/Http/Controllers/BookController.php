<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::query()->with(['category', 'author', 'galleries']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('isbn', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('author', fn ($author) => $author->where('name', 'like', "%{$search}%")));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        $sortBy = $request->input('sort_by', 'published_at');
        $sortDir = $request->input('sort_dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['created_at', 'published_at', 'title', 'year', 'views', 'downloads'];
        $query->orderBy(in_array($sortBy, $allowedSorts) ? $sortBy : 'published_at', $sortDir);

        return $query->paginate($request->input('per_page', 9));
    }

    public function show(Book $book)
    {
        if ($book->status === 'published') {
            $book->increment('views');
        }

        return response()->json($book->load(['category', 'author', 'galleries']));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data = $this->storeFiles($request, $data);
        $data['featured'] = $request->boolean('featured');
        $data['published_at'] = $data['status'] === 'published'
            ? ($request->input('published_at') ?: now())
            : null;

        $book = Book::create($data);
        $this->storeGalleries($request, $book);

        return response()->json($book->load(['category', 'author', 'galleries']), 201);
    }

    public function update(Request $request, Book $book)
    {
        $data = $this->validatedData($request);

        if ($data['title'] !== $book->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $book->id);
        }

        $data = $this->storeFiles($request, $data, $book);
        $data['featured'] = $request->boolean('featured');
        $data['published_at'] = $data['status'] === 'published'
            ? ($request->input('published_at') ?: ($book->published_at ?: now()))
            : null;

        $book->update($data);
        $this->storeGalleries($request, $book);

        return response()->json($book->load(['category', 'author', 'galleries']));
    }

    public function destroy(Book $book)
    {
        foreach (['cover', 'preview_file', 'full_file'] as $field) {
            if ($book->{$field}) Storage::disk('public')->delete($book->{$field});
        }

        foreach ($book->galleries as $gallery) {
            if ($gallery->image) Storage::disk('public')->delete($gallery->image);
        }

        $book->delete();

        return response()->json(['message' => 'Buku berhasil dihapus.']);
    }

    public function download(Book $book)
    {
        if (!$book->full_file || !Storage::disk('public')->exists($book->full_file)) {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        $book->increment('downloads');

        return Storage::disk('public')->download($book->full_file);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'category_id' => 'required|exists:book_categories,id',
            'author_id' => 'required|exists:authors,id',
            'title' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1000|max:9999',
            'publisher' => 'nullable|string|max:255',
            'pages' => 'nullable|integer|min:1',
            'language' => 'nullable|string|max:255',
            'cover' => 'nullable|image|max:5120',
            'preview_file' => 'nullable|mimes:pdf|max:51200',
            'full_file' => 'nullable|mimes:pdf|max:102400',
            'description' => 'nullable|string',
            'table_of_contents' => 'nullable|string',
            'tags' => 'nullable|string|max:255',
            'status' => 'required|in:draft,review,published,archived',
            'featured' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'galleries.*' => 'nullable|image|max:5120',
            'remove_cover' => 'nullable|boolean',
            'remove_preview_file' => 'nullable|boolean',
            'remove_full_file' => 'nullable|boolean',
        ]);
    }

    private function storeFiles(Request $request, array $data, ?Book $book = null): array
    {
        foreach (['cover', 'preview_file', 'full_file'] as $field) {
            if ($request->boolean("remove_{$field}") && $book?->{$field}) {
                Storage::disk('public')->delete($book->{$field});
                $data[$field] = null;
            }

            if ($request->hasFile($field)) {
                if ($book?->{$field}) Storage::disk('public')->delete($book->{$field});
                $data[$field] = $request->file($field)->store('books', 'public');
            }

            unset($data["remove_{$field}"]);
        }

        unset($data['galleries']);

        return $data;
    }

    private function storeGalleries(Request $request, Book $book): void
    {
        if (!$request->hasFile('galleries')) {
            return;
        }

        foreach ($request->file('galleries') as $image) {
            BookGallery::create([
                'book_id' => $book->id,
                'image' => $image->store('books/gallery', 'public'),
                'created_at' => now(),
            ]);
        }
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 2;

        while (Book::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
