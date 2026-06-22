<?php

namespace App\Http\Controllers;

use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NewsCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = NewsCategory::query()->withCount('news');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%"));
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_active') && $request->is_active !== 'all') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->boolean('all')) {
            return $query->orderBy('name')->get();
        }

        return $query->orderBy('name')->paginate($request->input('per_page', 10));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);

        return response()->json(NewsCategory::create($data)->loadCount('news'), 201);
    }

    public function show(NewsCategory $newsCategory)
    {
        return response()->json($newsCategory->loadCount('news'));
    }

    public function update(Request $request, NewsCategory $newsCategory)
    {
        $data = $this->validated($request);

        if ($data['name'] !== $newsCategory->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $newsCategory->id);
        }

        $newsCategory->update($data);

        return response()->json($newsCategory->fresh()->loadCount('news'));
    }

    public function destroy(NewsCategory $newsCategory)
    {
        if ($newsCategory->news()->exists() || $newsCategory->legacyNews()->exists()) {
            return response()->json(['message' => 'Kategori masih digunakan oleh berita.'], 422);
        }

        $newsCategory->delete();

        return response()->json(['message' => 'Kategori news berhasil dihapus.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['Artikel', 'Video', 'Gambar'])],
            'description' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (NewsCategory::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
