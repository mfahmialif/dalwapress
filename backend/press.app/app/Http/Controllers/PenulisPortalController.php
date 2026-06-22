<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PenulisPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $this->authorizePenulis($request);

        $query = $this->ownedNews($request);

        return response()->json([
            'stats' => [
                'total' => (clone $query)->count(),
                'published' => (clone $query)->where('status', 'Published')->count(),
                'draft' => (clone $query)->where('status', 'Draft')->count(),
            ],
            'latest_news' => (clone $query)
                ->with(['category', 'categories', 'author:id,name,email'])
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    public function index(Request $request)
    {
        $this->authorizePenulis($request);

        $query = $this->ownedNews($request)->with(['category', 'categories', 'author:id,name,email']);
        $this->applyFilters($query, $request);

        return $query->latest()->paginate($request->input('per_page', 10));
    }

    public function show(Request $request, News $news)
    {
        $this->authorizePenulis($request);
        $this->authorizeOwned($request, $news);

        return response()->json($news->load(['category', 'categories', 'author:id,name,email']));
    }

    public function store(Request $request)
    {
        $this->authorizePenulis($request);

        $data = $this->validatedData($request);
        $categoryIds = $this->categoryIds($request);
        $data['news_category_id'] = $categoryIds[0];
        $data['author_id'] = $request->user()->id;
        $data['created_by'] = $request->user()->id;
        $this->storeFiles($request, $data);

        $news = News::create($data);
        $news->categories()->sync($categoryIds);

        return response()->json($news->load(['category', 'categories', 'author:id,name,email']), 201);
    }

    public function update(Request $request, News $news)
    {
        $this->authorizePenulis($request);
        $this->authorizeOwned($request, $news);

        $data = $this->validatedData($request);
        $categoryIds = $this->categoryIds($request);
        $data['news_category_id'] = $categoryIds[0];
        $data['author_id'] = $request->user()->id;
        $this->storeFiles($request, $data, $news);
        $this->removeFiles($request, $data, $news);

        $news->update($data);
        $news->categories()->sync($categoryIds);

        return response()->json($news->load(['category', 'categories', 'author:id,name,email']));
    }

    public function destroy(Request $request, News $news)
    {
        $this->authorizePenulis($request);
        $this->authorizeOwned($request, $news);

        if ($news->image_path) Storage::disk('public')->delete($news->image_path);
        if ($news->video_path) Storage::disk('public')->delete($news->video_path);
        $news->delete();

        return response()->json(['message' => 'Artikel berhasil dihapus.']);
    }

    public function profile(Request $request)
    {
        $this->authorizePenulis($request);

        return response()->json($request->user()->load('role:id,name'));
    }

    private function ownedNews(Request $request)
    {
        return News::query()->where('author_id', $request->user()->id);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('body', 'like', "%{$search}%"));
        }

        if ($request->filled('category_id')) {
            $categoryId = $request->category_id;
            $query->where(fn ($q) => $q
                ->where('news_category_id', $categoryId)
                ->orWhereHas('categories', fn ($categoryQuery) => $categoryQuery->whereKey($categoryId)));
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
    }

    private function validatedData(Request $request): array
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'news_category_id' => 'required_without:news_category_ids|nullable|exists:news_categories,id',
            'news_category_ids' => 'required_without:news_category_id|array|min:1',
            'news_category_ids.*' => 'exists:news_categories,id',
            'status' => 'in:Published,Draft',
            'body' => 'nullable|string',
            'speaker' => 'nullable|string|max:255',
            'duration' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:5120',
            'video' => 'nullable|mimes:mp4,webm,ogg|max:51200',
        ]);

        return $request->only(['title', 'body', 'speaker', 'duration', 'status']);
    }

    private function storeFiles(Request $request, array &$data, ?News $news = null): void
    {
        if ($request->hasFile('image')) {
            if ($news?->image_path) Storage::disk('public')->delete($news->image_path);
            $data['image_path'] = $request->file('image')->store('news', 'public');
        }

        if ($request->hasFile('video')) {
            if ($news?->video_path) Storage::disk('public')->delete($news->video_path);
            $data['video_path'] = $request->file('video')->store('news', 'public');
        }
    }

    private function removeFiles(Request $request, array &$data, News $news): void
    {
        if ($request->input('remove_image')) {
            if ($news->image_path) Storage::disk('public')->delete($news->image_path);
            $data['image_path'] = null;
        }

        if ($request->input('remove_video')) {
            if ($news->video_path) Storage::disk('public')->delete($news->video_path);
            $data['video_path'] = null;
        }
    }

    private function authorizeOwned(Request $request, News $news): void
    {
        abort_if((int) $news->author_id !== (int) $request->user()->id, 403);
    }

    private function authorizePenulis(Request $request): void
    {
        abort_if($request->user()->role?->name !== 'Penulis', 403);
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
