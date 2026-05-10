<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Submission;
use App\Models\SubmissionRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthorPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $this->authorizeAuthor($request);
        $user = $request->user();
        $submissions = Submission::where('user_id', $user->id);
        $latest = (clone $submissions)->with(['category', 'reviews'])->latest('submitted_at')->first();

        return response()->json([
            'stats' => [
                'total_submission' => (clone $submissions)->count(),
                'accepted' => (clone $submissions)->where('status', 'accepted')->count(),
                'revision' => (clone $submissions)->where('status', 'revision')->count(),
                'under_review' => (clone $submissions)->where('status', 'under_review')->count(),
                'published' => $this->publishedBooksQuery($user->id)->count(),
            ],
            'latest_submission' => $latest,
            'notifications' => $this->notificationsFor($user->id)->take(5)->values(),
        ]);
    }

    public function submissions(Request $request)
    {
        $this->authorizeAuthor($request);
        $query = Submission::query()
            ->where('user_id', $request->user()->id)
            ->with(['category', 'reviews' => fn ($q) => $q->latest('created_at')]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->latest('submitted_at')->paginate($request->input('per_page', 10));
    }

    public function storeSubmission(Request $request)
    {
        $this->authorizeAuthor($request);
        $data = $request->validate([
            'category_id' => 'required|exists:book_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'note' => 'nullable|string',
            'manuscript_file' => 'required|mimes:pdf,doc,docx|max:51200',
            'cover_file' => 'nullable|image|max:5120',
        ]);

        $user = $request->user()->load('author');
        $data['user_id'] = $user->id;
        $data['author_name'] = $user->author?->name ?: $user->name;
        $data['email'] = $user->email;
        $data['phone'] = $user->author?->phone;
        $data['slug'] = $this->uniqueSubmissionSlug($data['title']);
        $data['status'] = 'submitted';
        $data['submitted_at'] = now();
        $data['manuscript_file'] = $request->file('manuscript_file')->store('submissions/manuscripts', 'public');

        if ($request->hasFile('cover_file')) {
            $data['cover_file'] = $request->file('cover_file')->store('submissions/covers', 'public');
        }

        return response()->json(Submission::create($data)->load(['category', 'reviews', 'revisions']), 201);
    }

    public function showSubmission(Request $request, Submission $submission)
    {
        $this->authorizeAuthor($request);
        $this->authorizeOwnedSubmission($request, $submission);

        return response()->json($submission->load(['category', 'reviews', 'revisions']));
    }

    public function updateSubmission(Request $request, Submission $submission)
    {
        $this->authorizeAuthor($request);
        $this->authorizeOwnedSubmission($request, $submission);

        if (in_array($submission->status, ['accepted', 'published'], true)) {
            return response()->json(['message' => 'Submission yang sudah accepted/published tidak dapat diedit.'], 422);
        }

        $data = $request->validate([
            'category_id' => 'required|exists:book_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'note' => 'nullable|string',
            'manuscript_file' => 'nullable|mimes:pdf,doc,docx|max:51200',
            'cover_file' => 'nullable|image|max:5120',
        ]);

        if ($data['title'] !== $submission->title) {
            $data['slug'] = $this->uniqueSubmissionSlug($data['title'], $submission->id);
        }

        foreach (['manuscript_file', 'cover_file'] as $field) {
            if ($request->hasFile($field)) {
                if ($submission->{$field}) Storage::disk('public')->delete($submission->{$field});
                $folder = $field === 'cover_file' ? 'submissions/covers' : 'submissions/manuscripts';
                $data[$field] = $request->file($field)->store($folder, 'public');
            }
        }

        $submission->update($data);

        return response()->json($submission->load(['category', 'reviews', 'revisions']));
    }

    public function deleteSubmission(Request $request, Submission $submission)
    {
        $this->authorizeAuthor($request);
        $this->authorizeOwnedSubmission($request, $submission);

        if ($submission->status !== 'submitted') {
            return response()->json(['message' => 'Hanya draft/submission awal yang dapat dihapus.'], 422);
        }

        foreach (['manuscript_file', 'cover_file'] as $field) {
            if ($submission->{$field}) Storage::disk('public')->delete($submission->{$field});
        }

        $submission->delete();

        return response()->json(['message' => 'Submission berhasil dihapus.']);
    }

    public function uploadRevision(Request $request, Submission $submission)
    {
        $this->authorizeAuthor($request);
        $this->authorizeOwnedSubmission($request, $submission);

        if ($submission->status !== 'revision') {
            return response()->json(['message' => 'Upload revisi hanya tersedia untuk status revision.'], 422);
        }

        $data = $request->validate([
            'revision_file' => 'required|mimes:pdf,doc,docx|max:51200',
            'note' => 'nullable|string',
        ]);

        $revision = SubmissionRevision::create([
            'submission_id' => $submission->id,
            'user_id' => $request->user()->id,
            'revision_file' => $request->file('revision_file')->store('submissions/revisions', 'public'),
            'note' => $data['note'] ?? null,
        ]);

        $submission->update(['status' => 'under_review']);

        return response()->json([
            'submission' => $submission->fresh()->load(['category', 'reviews', 'revisions']),
            'revision' => $revision,
        ], 201);
    }

    public function publishedBooks(Request $request)
    {
        $this->authorizeAuthor($request);
        return $this->publishedBooksQuery($request->user()->id)
            ->with(['category', 'author'])
            ->latest('published_at')
            ->paginate($request->input('per_page', 9));
    }

    public function profile(Request $request)
    {
        $this->authorizeAuthor($request);
        return response()->json($request->user()->load('role:id,name', 'author'));
    }

    public function updateProfile(Request $request)
    {
        $this->authorizeAuthor($request);
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'bio' => 'nullable|string',
            'institution' => 'nullable|string|max:255',
            'social_media' => 'nullable|array',
            'social_media.*' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:5120',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            ...($request->filled('password') ? ['password' => Hash::make($data['password'])] : []),
        ]);

        $author = $user->author ?: new Author(['user_id' => $user->id]);
        $authorData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'bio' => $data['bio'] ?? null,
            'institution' => $data['institution'] ?? null,
            'social_media' => $data['social_media'] ?? null,
        ];

        if (!$author->exists || $author->name !== $data['name']) {
            $authorData['slug'] = $this->uniqueAuthorSlug($data['name'], $author->id);
        }

        if ($request->hasFile('photo')) {
            if ($author->photo) Storage::disk('public')->delete($author->photo);
            $authorData['photo'] = $request->file('photo')->store('authors', 'public');
        }

        $author->fill($authorData)->save();

        return response()->json($user->fresh()->load('role:id,name', 'author'));
    }

    public function notifications(Request $request)
    {
        $this->authorizeAuthor($request);
        return response()->json($this->notificationsFor($request->user()->id)->values());
    }

    public function activity(Request $request)
    {
        $this->authorizeAuthor($request);
        $items = Submission::where('user_id', $request->user()->id)
            ->with('revisions')
            ->latest('updated_at')
            ->get()
            ->flatMap(function (Submission $submission) {
                $activity = [[
                    'type' => 'submission',
                    'label' => 'Submit naskah',
                    'title' => $submission->title,
                    'created_at' => $submission->submitted_at,
                ]];

                if ($submission->reviewed_at) {
                    $activity[] = [
                        'type' => 'status',
                        'label' => 'Perubahan status: ' . $submission->status,
                        'title' => $submission->title,
                        'created_at' => $submission->reviewed_at,
                    ];
                }

                foreach ($submission->revisions as $revision) {
                    $activity[] = [
                        'type' => 'revision',
                        'label' => 'Upload revisi',
                        'title' => $submission->title,
                        'created_at' => $revision->created_at,
                    ];
                }

                return $activity;
            })
            ->sortByDesc('created_at')
            ->values();

        return response()->json($items);
    }

    public function bookmarks(Request $request)
    {
        $this->authorizeAuthor($request);
        return response()->json([]);
    }

    private function authorizeAuthor(Request $request): void
    {
        abort_if($request->user()->role?->name !== 'Author', 403);
    }

    private function publishedBooksQuery(int $userId)
    {
        return Book::query()
            ->where('status', 'published')
            ->whereHas('author', fn ($query) => $query->where('user_id', $userId));
    }

    private function notificationsFor(int $userId)
    {
        return Submission::where('user_id', $userId)
            ->with(['reviews' => fn ($q) => $q->latest('created_at')])
            ->latest('updated_at')
            ->get()
            ->flatMap(function (Submission $submission) {
                $items = [];

                if ($submission->status === 'revision') {
                    $items[] = ['type' => 'revision', 'message' => 'Ada revisi untuk ' . $submission->title, 'created_at' => $submission->reviewed_at];
                }

                if ($submission->status === 'accepted') {
                    $items[] = ['type' => 'accepted', 'message' => 'Submission diterima: ' . $submission->title, 'created_at' => $submission->reviewed_at];
                }

                if ($submission->status === 'published') {
                    $items[] = ['type' => 'published', 'message' => 'Buku publish: ' . $submission->title, 'created_at' => $submission->reviewed_at];
                }

                foreach ($submission->reviews as $review) {
                    if ($review->note) {
                        $items[] = ['type' => 'editor', 'message' => 'Pesan editor untuk ' . $submission->title, 'note' => $review->note, 'created_at' => $review->created_at];
                    }
                }

                return $items;
            })
            ->sortByDesc('created_at');
    }

    private function authorizeOwnedSubmission(Request $request, Submission $submission): void
    {
        abort_if((int) $submission->user_id !== (int) $request->user()->id, 403);
    }

    private function uniqueSubmissionSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 2;

        while (Submission::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function uniqueAuthorSlug(string $name, ?int $ignoreId = null): string
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
