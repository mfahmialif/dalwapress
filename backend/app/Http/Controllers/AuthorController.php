<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthorController extends Controller
{
    public function index(Request $request)
    {
        $query = Author::query()->withCount('books');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('institution', 'like', "%{$search}%"));
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
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'photo' => 'nullable|image|max:5120',
            'bio' => 'nullable|string',
            'institution' => 'nullable|string|max:255',
        ]);

        $data['slug'] = $this->uniqueSlug($data['name']);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('authors', 'public');
        }

        return response()->json(Author::create($data), 201);
    }

    public function show(Author $author)
    {
        return response()->json($author->load(['books.category'])->loadCount('books'));
    }

    public function update(Request $request, Author $author)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'photo' => 'nullable|image|max:5120',
            'bio' => 'nullable|string',
            'institution' => 'nullable|string|max:255',
            'remove_photo' => 'nullable|boolean',
        ]);

        if ($data['name'] !== $author->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $author->id);
        }

        if ($request->boolean('remove_photo')) {
            if ($author->photo) Storage::disk('public')->delete($author->photo);
            $data['photo'] = null;
        }

        if ($request->hasFile('photo')) {
            if ($author->photo) Storage::disk('public')->delete($author->photo);
            $data['photo'] = $request->file('photo')->store('authors', 'public');
        }

        unset($data['remove_photo']);
        $author->update($data);

        return response()->json($author);
    }

    public function destroy(Author $author)
    {
        if ($author->books()->exists()) {
            return response()->json(['message' => 'Author masih memiliki buku.'], 422);
        }

        if ($author->photo) Storage::disk('public')->delete($author->photo);
        $author->delete();

        return response()->json(['message' => 'Author berhasil dihapus.']);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (Author::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
