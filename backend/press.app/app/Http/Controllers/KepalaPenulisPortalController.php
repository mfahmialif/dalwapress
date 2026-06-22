<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class KepalaPenulisPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $this->authorizeKepalaPenulis($request);

        $query = $this->managedNews();

        return response()->json([
            'stats' => [
                'total' => (clone $query)->count(),
                'published' => (clone $query)->where('status', 'Published')->count(),
                'draft' => (clone $query)->where('status', 'Draft')->count(),
                'writers' => $this->writerUsers()->count(),
            ],
            'latest_news' => (clone $query)
                ->with(['category', 'categories', 'author:id,name,email'])
                ->latest()
                ->limit(6)
                ->get(),
        ]);
    }

    public function index(Request $request)
    {
        $this->authorizeKepalaPenulis($request);

        $query = $this->managedNews()->with(['category', 'categories', 'author:id,name,email']);
        $this->applyFilters($query, $request);

        return $query->latest()->paginate($request->input('per_page', 10));
    }

    public function show(Request $request, News $news)
    {
        $this->authorizeKepalaPenulis($request);
        $this->authorizeManagedNews($news);

        return response()->json($news->load(['category', 'categories', 'author:id,name,email']));
    }

    public function store(Request $request)
    {
        $this->authorizeKepalaPenulis($request);

        $data = $this->validatedNewsData($request);
        $categoryIds = $this->categoryIds($request);
        $data['news_category_id'] = $categoryIds[0];
        $this->ensureManagedAuthor($data['author_id']);
        $data['created_by'] = $request->user()->id;
        $this->storeFiles($request, $data);

        $news = News::create($data);
        $news->categories()->sync($categoryIds);

        return response()->json($news->load(['category', 'categories', 'author:id,name,email']), 201);
    }

    public function update(Request $request, News $news)
    {
        $this->authorizeKepalaPenulis($request);
        $this->authorizeManagedNews($news);

        $data = $this->validatedNewsData($request);
        $categoryIds = $this->categoryIds($request);
        $data['news_category_id'] = $categoryIds[0];
        $this->ensureManagedAuthor($data['author_id']);
        $this->storeFiles($request, $data, $news);
        $this->removeFiles($request, $data, $news);

        $news->update($data);
        $news->categories()->sync($categoryIds);

        return response()->json($news->load(['category', 'categories', 'author:id,name,email']));
    }

    public function destroy(Request $request, News $news)
    {
        $this->authorizeKepalaPenulis($request);
        $this->authorizeManagedNews($news);

        if ($news->image_path) Storage::disk('public')->delete($news->image_path);
        if ($news->video_path) Storage::disk('public')->delete($news->video_path);
        $news->delete();

        return response()->json(['message' => 'Artikel berhasil dihapus.']);
    }

    public function writers(Request $request)
    {
        $this->authorizeKepalaPenulis($request);

        $query = $this->writerUsers()->with('role:id,name')->withCount('authoredNews');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%"));
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return $query->orderBy('name')->paginate($request->input('per_page', 10));
    }

    public function storeWriter(Request $request)
    {
        $this->authorizeKepalaPenulis($request);

        $data = $this->validatedWriterData($request);
        $data['role_id'] = $this->penulisRole()->id;
        $data['password'] = Hash::make($data['password']);

        return response()->json(User::create($data)->load('role:id,name'), 201);
    }

    public function updateWriter(Request $request, User $user)
    {
        $this->authorizeKepalaPenulis($request);
        $this->authorizeWriterUser($user);

        $data = $this->validatedWriterData($request, $user);
        $data['role_id'] = $this->penulisRole()->id;
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json($user->load('role:id,name'));
    }

    public function destroyWriter(Request $request, User $user)
    {
        $this->authorizeKepalaPenulis($request);
        $this->authorizeWriterUser($user);

        $user->update(['status' => 'Inactive']);

        return response()->json(['message' => 'Akun penulis dinonaktifkan.']);
    }

    public function writerOptions(Request $request)
    {
        $this->authorizeKepalaPenulis($request);

        return $this->writerUsers()
            ->where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role_id']);
    }

    public function profile(Request $request)
    {
        $this->authorizeKepalaPenulis($request);

        return response()->json($request->user()->load('role:id,name'));
    }

    private function managedNews()
    {
        return News::query()
            ->whereHas('author.role', fn ($query) => $query->where('name', 'Penulis'));
    }

    private function writerUsers()
    {
        return User::query()->whereHas('role', fn ($query) => $query->where('name', 'Penulis'));
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

        if ($request->filled('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
    }

    private function validatedNewsData(Request $request): array
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'news_category_id' => 'required_without:news_category_ids|nullable|exists:news_categories,id',
            'news_category_ids' => 'required_without:news_category_id|array|min:1',
            'news_category_ids.*' => 'exists:news_categories,id',
            'author_id' => 'required|exists:users,id',
            'status' => 'in:Published,Draft',
            'body' => 'nullable|string',
            'speaker' => 'nullable|string|max:255',
            'duration' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:5120',
            'video' => 'nullable|mimes:mp4,webm,ogg|max:51200',
        ]);

        return $request->only(['title', 'author_id', 'body', 'speaker', 'duration', 'status']);
    }

    private function validatedWriterData(Request $request, ?User $user = null): array
    {
        $passwordRule = $user ? 'nullable|string|min:6' : 'required|string|min:6';

        $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user?->id)],
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user?->id)],
            'password' => $passwordRule,
            'status' => 'in:Active,Inactive',
        ]);

        return $request->only(['username', 'name', 'email', 'password', 'status']);
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

    private function ensureManagedAuthor(int $authorId): void
    {
        abort_unless($this->writerUsers()->whereKey($authorId)->exists(), 422, 'Penulis tidak valid.');
    }

    private function authorizeManagedNews(News $news): void
    {
        abort_unless($this->managedNews()->whereKey($news->id)->exists(), 403);
    }

    private function authorizeWriterUser(User $user): void
    {
        abort_unless($user->role?->name === 'Penulis', 403);
    }

    private function penulisRole(): Role
    {
        return Role::where('name', 'Penulis')->firstOrFail();
    }

    private function authorizeKepalaPenulis(Request $request): void
    {
        abort_if($request->user()->role?->name !== 'Kepala Penulis', 403);
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
