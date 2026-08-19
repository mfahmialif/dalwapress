<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RestoreController extends Controller
{
    private $models = [
        'books' => \App\Models\Book::class,
        'submissions' => \App\Models\Submission::class,
        'news' => \App\Models\News::class,
        'authors' => \App\Models\Author::class,
        'book-categories' => \App\Models\BookCategory::class,
        'news-categories' => \App\Models\NewsCategory::class,
        'users' => \App\Models\User::class,
        'roles' => \App\Models\Role::class,
        'royalties' => \App\Models\Royalty::class,
    ];

    public function index(Request $request)
    {
        $type = $request->input('type', 'books');
        
        if (!array_key_exists($type, $this->models)) {
            return response()->json(['message' => 'Tipe tidak valid.'], 400);
        }

        $modelClass = $this->models[$type];
        
        $query = $modelClass::onlyTrashed()->with('deleter:id,name');

        if ($request->filled('search')) {
            $search = $request->search;
            if ($type === 'books' || $type === 'submissions' || $type === 'news') {
                $query->where('title', 'like', "%{$search}%");
            } else if ($type === 'authors' || $type === 'users' || $type === 'roles' || $type === 'book-categories' || $type === 'news-categories') {
                $query->where('name', 'like', "%{$search}%");
            }
        }

        $data = $query->orderBy('deleted_at', 'desc')->paginate($request->input('per_page', 10));

        return response()->json($data);
    }

    public function restore($type, $id, Request $request)
    {
        if (!array_key_exists($type, $this->models)) {
            return response()->json(['message' => 'Tipe tidak valid.'], 400);
        }

        $modelClass = $this->models[$type];
        $record = $modelClass::onlyTrashed()->findOrFail($id);

        $overwrite = $request->boolean('overwrite', false);
        $duplicate = null;

        if ($type === 'books') {
            if ($record->submission_id) {
                $duplicate = \App\Models\Book::where('submission_id', $record->submission_id)->first();
            } else {
                $duplicate = \App\Models\Book::where('title', $record->title)->first();
            }
        } elseif ($type === 'submissions') {
            $duplicate = \App\Models\Submission::where('title', $record->title)->first();
        } elseif ($type === 'news') {
            $duplicate = \App\Models\News::where('title', $record->title)->first();
        } elseif ($type === 'authors') {
            $duplicate = \App\Models\Author::where('name', $record->name)->first();
        } elseif ($type === 'book-categories') {
            $duplicate = \App\Models\BookCategory::where('name', $record->name)->first();
        } elseif ($type === 'news-categories') {
            $duplicate = \App\Models\NewsCategory::where('name', $record->name)->first();
        } elseif ($type === 'users') {
            $duplicate = \App\Models\User::where('username', $record->username)
                ->orWhere('email', $record->email)
                ->first();
        } elseif ($type === 'roles') {
            $duplicate = \App\Models\Role::where('name', $record->name)->first();
        }

        if ($duplicate) {
            if (!$overwrite) {
                $displayName = $duplicate->title ?? $duplicate->name ?? $duplicate->username ?? "ID: {$duplicate->id}";
                return response()->json([
                    'error' => 'duplicate',
                    'message' => 'Data aktif dengan identitas serupa sudah ada di sistem.',
                    'duplicate_details' => "Data aktif saat ini: \"{$displayName}\" (ID: {$duplicate->id})."
                ], 409);
            }

            // User chose to overwrite: permanently delete the currently active duplicate
            if ($type === 'books') {
                foreach (['cover', 'preview_file', 'full_file'] as $field) {
                    if ($duplicate->{$field}) \Illuminate\Support\Facades\Storage::disk('public')->delete($duplicate->{$field});
                }
            } elseif ($type === 'submissions') {
                foreach (['manuscript_file', 'cover_file'] as $field) {
                    if ($duplicate->{$field}) \Illuminate\Support\Facades\Storage::disk('public')->delete($duplicate->{$field});
                }
            } elseif ($type === 'news') {
                if ($duplicate->image_path) \Illuminate\Support\Facades\Storage::disk('public')->delete($duplicate->image_path);
                if ($duplicate->video_path) \Illuminate\Support\Facades\Storage::disk('public')->delete($duplicate->video_path);
            } elseif ($type === 'authors') {
                if ($duplicate->photo) \Illuminate\Support\Facades\Storage::disk('public')->delete($duplicate->photo);
            }

            $duplicate->forceDelete();
        }

        $record->restore();

        return response()->json([
            'message' => 'Data berhasil dipulihkan.',
            'data' => $record
        ]);
    }

    public function forceDelete($type, $id)
    {
        if (!array_key_exists($type, $this->models)) {
            return response()->json(['message' => 'Tipe tidak valid.'], 400);
        }

        $modelClass = $this->models[$type];
        $record = $modelClass::onlyTrashed()->findOrFail($id);
        
        // Custom logic to clean files when force deleting
        if ($type === 'books') {
            foreach (['cover', 'preview_file', 'full_file'] as $field) {
                if ($record->{$field}) \Illuminate\Support\Facades\Storage::disk('public')->delete($record->{$field});
            }
        } elseif ($type === 'submissions') {
            foreach (['manuscript_file', 'cover_file'] as $field) {
                if ($record->{$field}) \Illuminate\Support\Facades\Storage::disk('public')->delete($record->{$field});
            }
        } elseif ($type === 'news') {
            if ($record->image_path) \Illuminate\Support\Facades\Storage::disk('public')->delete($record->image_path);
            if ($record->video_path) \Illuminate\Support\Facades\Storage::disk('public')->delete($record->video_path);
        } elseif ($type === 'authors') {
            if ($record->photo) \Illuminate\Support\Facades\Storage::disk('public')->delete($record->photo);
        }

        $record->forceDelete();

        return response()->json([
            'message' => 'Data berhasil dihapus permanen.'
        ]);
    }
}
