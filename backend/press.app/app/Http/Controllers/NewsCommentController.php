<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\NewsComment;
use App\Models\News;
use App\Models\CommentLike;

class NewsCommentController extends Controller
{
    public function index($newsId)
    {
        $userId = auth('sanctum')->id();

        $comments = NewsComment::where('news_id', $newsId)
            ->whereNull('parent_id')
            ->with(['replies' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // Add is_liked status
        $comments->each(function ($comment) use ($userId) {
            $comment->is_liked = $userId ? CommentLike::where('user_id', $userId)->where('news_comment_id', $comment->id)->exists() : false;
            if ($comment->replies) {
                $comment->replies->each(function ($reply) use ($userId) {
                    $reply->is_liked = $userId ? CommentLike::where('user_id', $userId)->where('news_comment_id', $reply->id)->exists() : false;
                });
            }
        });

        return response()->json($comments);
    }

    public function store(Request $request, $newsId)
    {
        $request->validate([
            'name' => 'required|string|max:60',
            'email' => 'nullable|email|max:90',
            'message' => 'required|string|max:600',
            'parent_id' => 'nullable|exists:news_comments,id',
        ]);

        $comment = NewsComment::create([
            'news_id' => $newsId,
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message,
            'parent_id' => $request->parent_id,
        ]);

        return response()->json($comment, 201);
    }

    public function like($commentId)
    {
        $userId = auth('sanctum')->id();
        
        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $comment = NewsComment::findOrFail($commentId);

        // Check if already liked
        $exists = CommentLike::where('user_id', $userId)
            ->where('news_comment_id', $commentId)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Anda sudah menyukai komentar ini.', 'likes' => $comment->likes], 400);
        }

        // Create like
        CommentLike::create([
            'user_id' => $userId,
            'news_comment_id' => $commentId,
        ]);

        $comment->increment('likes');

        return response()->json(['likes' => $comment->likes, 'is_liked' => true]);
    }
}
