<?php

namespace App\Http\Controllers;

use App\Models\BookCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = BookCategory::query()->withCount('books');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"));
        }

        if ($request->boolean('all')) {
            return $query->orderBy('name')->get();
        }

        return $query->orderBy('name')->paginate($request->input('per_page', 10));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data['slug'] = $this->uniqueSlug($data['name']);

        return response()->json(BookCategory::create($data), 201);
    }

    public function show(BookCategory $bookCategory)
    {
        return response()->json($bookCategory->loadCount('books'));
    }

    public function update(Request $request, BookCategory $bookCategory)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($data['name'] !== $bookCategory->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $bookCategory->id);
        }

        $bookCategory->update($data);

        return response()->json($bookCategory);
    }

    public function destroy(BookCategory $bookCategory)
    {
        if ($bookCategory->books()->exists() || $bookCategory->submissions()->exists()) {
            return response()->json(['message' => 'Kategori masih digunakan.'], 422);
        }

        $bookCategory->delete();

        return response()->json(['message' => 'Kategori buku berhasil dihapus.']);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (BookCategory::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
