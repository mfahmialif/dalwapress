<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $query = News::with(['category', 'categories', 'author:id,name,email', 'creator:id,name,email']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('body', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category_id')) {
            $categoryId = $request->category_id;
            $query->where(fn ($q) => $q
                ->where('news_category_id', $categoryId)
                ->orWhereHas('categories', fn ($categoryQuery) => $categoryQuery->whereKey($categoryId)));
        }

        if ($request->filled('category')) {
            $category = $request->category;
            $categoryFilter = function ($q) use ($category) {
                $q->where('slug', $category)
                    ->orWhere('name', $category)
                    ->orWhere('type', $category);
            };
            $query->where(fn ($q) => $q
                ->whereHas('category', $categoryFilter)
                ->orWhereHas('categories', $categoryFilter));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSort = ['created_at', 'title', 'news_category_id'];
        if (in_array($sortBy, $allowedSort)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderByDesc('created_at');
        }

        $perPage = $request->input('per_page', 6);

        return $query->paginate($perPage);
    }

    public function show(News $news)
    {
        return response()->json($news->load(['category', 'categories', 'author:id,name,email', 'creator:id,name,email']));
    }

    public function categories()
    {
        return NewsCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'news_category_id' => 'required_without:news_category_ids|nullable|exists:news_categories,id',
            'news_category_ids' => 'required_without:news_category_id|array|min:1',
            'news_category_ids.*' => 'exists:news_categories,id',
            'author_id' => 'nullable|exists:users,id',
            'status'   => 'in:Published,Draft',
            'image'    => 'nullable|image|max:5120',
            'video'    => 'nullable|mimes:mp4,webm,ogg|max:51200',
        ]);

        $this->ensureNewsAuthor($request->input('author_id'));

        $categoryIds = $this->categoryIds($request);
        $data = $request->only(['title', 'author_id', 'body', 'speaker', 'duration', 'status']);
        $data['news_category_id'] = $categoryIds[0];
        $data['created_by'] = $request->user()?->id;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('news', 'public');
        }
        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('news', 'public');
        }

        $news = News::create($data);
        $news->categories()->sync($categoryIds);

        return response()->json($news->load(['category', 'categories', 'author:id,name,email', 'creator:id,name,email']), 201);
    }

    public function update(Request $request, News $news)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'news_category_id' => 'required_without:news_category_ids|nullable|exists:news_categories,id',
            'news_category_ids' => 'required_without:news_category_id|array|min:1',
            'news_category_ids.*' => 'exists:news_categories,id',
            'author_id' => 'nullable|exists:users,id',
            'status'   => 'in:Published,Draft',
            'image'    => 'nullable|image|max:5120',
            'video'    => 'nullable|mimes:mp4,webm,ogg|max:51200',
        ]);

        $this->ensureNewsAuthor($request->input('author_id'));

        $categoryIds = $this->categoryIds($request);
        $data = $request->only(['title', 'author_id', 'body', 'speaker', 'duration', 'status']);
        $data['news_category_id'] = $categoryIds[0];

        if ($request->hasFile('image')) {
            if ($news->image_path) Storage::disk('public')->delete($news->image_path);
            $data['image_path'] = $request->file('image')->store('news', 'public');
        }
        if ($request->hasFile('video')) {
            if ($news->video_path) Storage::disk('public')->delete($news->video_path);
            $data['video_path'] = $request->file('video')->store('news', 'public');
        }
        if ($request->input('remove_image')) {
            if ($news->image_path) Storage::disk('public')->delete($news->image_path);
            $data['image_path'] = null;
        }
        if ($request->input('remove_video')) {
            if ($news->video_path) Storage::disk('public')->delete($news->video_path);
            $data['video_path'] = null;
        }

        $news->update($data);
        $news->categories()->sync($categoryIds);

        return response()->json($news->load(['category', 'categories', 'author:id,name,email', 'creator:id,name,email']));
    }

    public function destroy(News $news)
    {
        if ($news->image_path) Storage::disk('public')->delete($news->image_path);
        if ($news->video_path) Storage::disk('public')->delete($news->video_path);
        $news->delete();
        return response()->json(['message' => 'Konten berhasil dihapus.']);
    }

    public function uploadEditorFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200',
        ]);

        $file = $request->file('file');
        $mime = $file->getMimeType();

        if (!str_starts_with($mime, 'image/') && !str_starts_with($mime, 'video/')) {
            return response()->json(['message' => 'Tipe file tidak didukung.'], 422);
        }

        $path = $file->store('news/editor', 'public');

        return response()->json([
            'url' => '/storage/' . $path,
        ]);
    }

    public function deleteEditorFile(Request $request)
    {
        $request->validate([
            'url' => 'required|string',
        ]);

        $path = str_replace('/storage/', '', $request->input('url'));

        if (!str_starts_with($path, 'news/editor/')) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return response()->json(['message' => 'File berhasil dihapus.']);
        }

        return response()->json(['message' => 'File tidak ditemukan.'], 404);
    }

    public function authors()
    {
        return User::query()
            ->with('role:id,name')
            ->where('status', 'Active')
            ->whereHas('role', fn ($query) => $query->whereIn('name', ['Penulis', 'Kepala Penulis']))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role_id']);
    }

    private function ensureNewsAuthor($authorId): void
    {
        if (!$authorId) {
            return;
        }

        abort_unless(
            User::query()
                ->whereKey($authorId)
                ->whereHas('role', fn ($query) => $query->whereIn('name', ['Penulis', 'Kepala Penulis']))
                ->exists(),
            422,
            'Penulis berita tidak valid.'
        );
    }

    private function categoryIds(Request $request): array
    {
        $ids = $request->input('news_category_ids', []);
        if (!$ids && $request->filled('news_category_id')) {
            $ids = [$request->input('news_category_id')];
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        return collect($ids)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
