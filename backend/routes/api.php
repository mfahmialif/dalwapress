<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\AuthorPortalController;
use App\Http\Controllers\BookCategoryController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\EditorPortalController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NewsCommentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/news', [NewsController::class, 'index']);
Route::get('/news-categories', [NewsController::class, 'categories']);
Route::get('/news/{news}', [NewsController::class, 'show']);

Route::get('/news/{news}/comments', [NewsCommentController::class, 'index']);
Route::post('/news/{news}/comments', [NewsCommentController::class, 'store']);

Route::get('/book-categories', [BookCategoryController::class, 'index']);
Route::get('/authors', [AuthorController::class, 'index']);
Route::get('/authors/{author}', [AuthorController::class, 'show']);
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{book}', [BookController::class, 'show']);
Route::get('/books/{book}/download', [BookController::class, 'download']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/comments/{comment}/like', [NewsCommentController::class, 'like']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('author')->group(function () {
        Route::get('/dashboard', [AuthorPortalController::class, 'dashboard']);
        Route::get('/submissions', [AuthorPortalController::class, 'submissions']);
        Route::post('/submissions', [AuthorPortalController::class, 'storeSubmission']);
        Route::get('/submissions/{submission}', [AuthorPortalController::class, 'showSubmission']);
        Route::post('/submissions/{submission}', [AuthorPortalController::class, 'updateSubmission']);
        Route::delete('/submissions/{submission}', [AuthorPortalController::class, 'deleteSubmission']);
        Route::post('/submissions/{submission}/revision', [AuthorPortalController::class, 'uploadRevision']);
        Route::get('/books', [AuthorPortalController::class, 'publishedBooks']);
        Route::get('/profile', [AuthorPortalController::class, 'profile']);
        Route::post('/profile', [AuthorPortalController::class, 'updateProfile']);
        Route::get('/notifications', [AuthorPortalController::class, 'notifications']);
        Route::get('/activity', [AuthorPortalController::class, 'activity']);
        Route::get('/bookmarks', [AuthorPortalController::class, 'bookmarks']);
    });

    Route::prefix('editor')->group(function () {
        Route::get('/dashboard', [EditorPortalController::class, 'dashboard']);
        Route::get('/submissions', [EditorPortalController::class, 'submissions']);
        Route::get('/submissions/{submission}', [EditorPortalController::class, 'show']);
        Route::post('/submissions/{submission}/reviews', [EditorPortalController::class, 'review']);
        Route::get('/profile', [EditorPortalController::class, 'profile']);
    });

    Route::apiResource('roles', RoleController::class);
    Route::apiResource('users', UserController::class);

    Route::apiResource('book-categories', BookCategoryController::class)->except(['index']);
    Route::apiResource('authors', AuthorController::class)->except(['index', 'show']);
    Route::apiResource('books', BookController::class)->except(['index', 'show']);
    Route::apiResource('submissions', SubmissionController::class)->except(['store']);
    Route::post('/submissions/{submission}/reviews', [SubmissionController::class, 'review']);
    Route::get('/editors', [SubmissionController::class, 'editors']);
    Route::post('/submissions/{submission}/assign-editors', [SubmissionController::class, 'assignEditors']);

    Route::post('/news', [NewsController::class, 'store']);
    Route::put('/news/{news}', [NewsController::class, 'update']);
    Route::delete('/news/{news}', [NewsController::class, 'destroy']);

    Route::post('/upload-editor', [NewsController::class, 'uploadEditorFile']);
    Route::post('/delete-editor-file', [NewsController::class, 'deleteEditorFile']);
});
