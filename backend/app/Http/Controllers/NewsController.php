<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $query = News::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('body', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSort = ['created_at', 'title', 'category'];
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
        return response()->json($news);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'category' => 'required|in:Artikel,Video,Gambar',
            'status'   => 'in:Published,Draft',
            'image'    => 'nullable|image|max:5120',
            'video'    => 'nullable|mimes:mp4,webm,ogg|max:51200',
        ]);

        $data = $request->only(['title', 'category', 'body', 'speaker', 'duration', 'status']);
        $data['created_by'] = $request->user()?->id;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('news', 'public');
        }
        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('news', 'public');
        }

        return response()->json(News::create($data), 201);
    }

    public function update(Request $request, News $news)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'category' => 'required|in:Artikel,Video,Gambar',
            'status'   => 'in:Published,Draft',
            'image'    => 'nullable|image|max:5120',
            'video'    => 'nullable|mimes:mp4,webm,ogg|max:51200',
        ]);

        $data = $request->only(['title', 'category', 'body', 'speaker', 'duration', 'status']);

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
        return response()->json($news);
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
}
