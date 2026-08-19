<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\SubmissionCoverAssignment;
use App\Models\SubmissionEditorAssignment;
use App\Models\SubmissionLayouterAssignment;
use App\Models\SubmissionRevision;
use App\Models\SubmissionReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Submission::query()->with(['category', 'reviews', 'editorAssignments.editor', 'layouterAssignments.layouter', 'coverAssignments.designer']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('author_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->orderByDesc('submitted_at')->paginate($request->input('per_page', 10));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:book_categories,id',
            'title' => 'required|string|max:255',
            'author_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'manuscript_file' => 'nullable|mimes:pdf,doc,docx|max:51200',
            'cover_file' => 'nullable|image|max:5120',
            'description' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['status'] = 'submitted';
        $data['submitted_at'] = now();

        if ($request->hasFile('manuscript_file')) {
            $data['manuscript_file'] = $request->file('manuscript_file')->store('submissions/manuscripts', 'public');
        }

        if ($request->hasFile('cover_file')) {
            $data['cover_file'] = $request->file('cover_file')->store('submissions/covers', 'public');
        }

        return response()->json(Submission::create($data)->load(['category', 'reviews']), 201);
    }

    public function show(Submission $submission)
    {
        return response()->json($submission->load([
            'category',
            'manuscriptVersions.user.role',
            'editorEvaluations.editor',
            'editorialDecisions.decider',
            'editorAssignments.editor',
            'editorAssignments.assigner',
            'layouterAssignments.layouter',
            'layouterAssignments.assigner',
            'coverAssignments.designer',
            'coverAssignments.assigner',
            'revisions.user.role',
            'reviews',
        ]));
    }

    public function update(Request $request, Submission $submission)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:book_categories,id',
            'title' => 'required|string|max:255',
            'author_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'note' => 'nullable|string',
            'status' => 'required|in:submitted,under_review,revision,accepted,rejected,published',
            'manuscript_file' => 'nullable|mimes:pdf,doc,docx|max:51200',
            'cover_file' => 'nullable|image|max:10240',
        ]);

        if ($data['title'] !== $submission->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $submission->id);
        }

        if (in_array($data['status'], ['revision', 'accepted', 'rejected', 'published'], true) && !$submission->reviewed_at) {
            $data['reviewed_at'] = now();
        }

        if ($request->hasFile('manuscript_file')) {
            if ($submission->manuscript_file) {
                Storage::disk('public')->delete($submission->manuscript_file);
            }
            $data['manuscript_file'] = $request->file('manuscript_file')->store('submissions/manuscripts', 'public');
        }

        if ($request->hasFile('cover_file')) {
            if ($submission->cover_file) {
                Storage::disk('public')->delete($submission->cover_file);
            }
            $data['cover_file'] = $request->file('cover_file')->store('submissions/covers', 'public');
        }

        $submission->update($data);

        return response()->json($submission->load(['category', 'reviews', 'editorAssignments.editor', 'layouterAssignments.layouter']));
    }

    public function destroy(Submission $submission)
    {
        $submission->delete();

        return response()->json(['message' => 'Submission berhasil dihapus.']);
    }

    public function review(Request $request, Submission $submission)
    {
        $data = $request->validate([
            'reviewer_name' => 'nullable|string|max:255',
            'reviewer_email' => 'nullable|email|max:255',
            'note' => 'nullable|string',
            'status' => 'required|in:revision,accepted,rejected',
        ]);

        $review = SubmissionReview::create([
            ...$data,
            'submission_id' => $submission->id,
            'created_at' => now(),
        ]);

        $submission->update([
            'status' => $data['status'],
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'submission' => $submission->fresh()->load(['category', 'reviews']),
            'review' => $review,
        ], 201);
    }

    public function editors()
    {
        $this->authorizeAdminOrOperator(request());

        return User::query()
            ->whereHas('role', fn ($query) => $query->where('name', 'Editor'))
            ->where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'username']);
    }

    public function layouters()
    {
        $this->authorizeAdminOrOperator(request());

        return User::query()
            ->whereHas('role', fn ($query) => $query->where('name', 'Layouter'))
            ->where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'username']);
    }

    public function coverDesigners()
    {
        $this->authorizeAdminOrOperator(request());

        return User::query()
            ->whereHas('role', fn ($query) => $query->where('name', 'Desain Cover'))
            ->where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'username']);
    }

    public function assignEditors(Request $request, Submission $submission)
    {
        $this->authorizeAdminOrOperator($request);

        $data = $request->validate([
            'primary_editor_id' => 'required|exists:users,id',
            'co_editor_ids' => 'nullable|array',
            'co_editor_ids.*' => 'integer|exists:users,id',
            'note' => 'nullable|string',
        ]);

        $primary = User::with('role')->findOrFail($data['primary_editor_id']);
        abort_if($primary->role?->name !== 'Editor', 422, 'Primary editor harus role Editor.');

        $coEditorIds = collect($data['co_editor_ids'] ?? [])
            ->filter(fn ($id) => (int) $id !== (int) $primary->id)
            ->unique()
            ->values();

        $validCoEditors = User::with('role')->whereIn('id', $coEditorIds)->get();
        abort_if($validCoEditors->count() !== $coEditorIds->count(), 422, 'Co-editor tidak valid.');
        abort_if($validCoEditors->contains(fn ($user) => $user->role?->name !== 'Editor'), 422, 'Co-editor harus role Editor.');

        SubmissionEditorAssignment::where('submission_id', $submission->id)->delete();

        SubmissionEditorAssignment::create([
            'submission_id' => $submission->id,
            'editor_id' => $primary->id,
            'assigned_by' => $request->user()->id,
            'role' => 'primary',
            'note' => $data['note'] ?? null,
        ]);

        foreach ($coEditorIds as $editorId) {
            SubmissionEditorAssignment::create([
                'submission_id' => $submission->id,
                'editor_id' => $editorId,
                'assigned_by' => $request->user()->id,
                'role' => 'co_editor',
                'note' => $data['note'] ?? null,
            ]);
        }

        if ($submission->status === 'submitted') {
            $submission->update(['status' => 'under_review']);
        }

        return response()->json($submission->fresh()->load(['category', 'reviews.editor', 'editorAssignments.editor', 'editorAssignments.assigner']));
    }

    public function assignLayouters(Request $request, Submission $submission)
    {
        $this->authorizeAdminOrOperator($request);

        $data = $request->validate([
            'layouter_ids' => 'nullable|array',
            'layouter_ids.*' => 'integer|exists:users,id',
            'note' => 'nullable|string',
        ]);

        $layouterIds = collect($data['layouter_ids'] ?? [])->unique()->values();

        if ($layouterIds->isNotEmpty()) {
            $validLayouters = User::with('role')->whereIn('id', $layouterIds)->get();
            abort_if($validLayouters->count() !== $layouterIds->count(), 422, 'Layouter tidak valid.');
            abort_if($validLayouters->contains(fn ($user) => $user->role?->name !== 'Layouter'), 422, 'Role harus Layouter.');
        }

        SubmissionLayouterAssignment::where('submission_id', $submission->id)->delete();

        foreach ($layouterIds as $layouterId) {
            SubmissionLayouterAssignment::create([
                'submission_id' => $submission->id,
                'layouter_id' => $layouterId,
                'assigned_by' => $request->user()->id,
                'note' => $data['note'] ?? null,
            ]);
        }

        if ($submission->status === 'submitted' && $layouterIds->isNotEmpty()) {
            $submission->update(['status' => 'under_review']);
        }

        return response()->json($submission->fresh()->load([
            'category',
            'reviews.editor',
            'revisions.user',
            'editorAssignments.editor',
            'editorAssignments.assigner',
            'layouterAssignments.layouter',
            'layouterAssignments.assigner',
            'coverAssignments.designer',
            'coverAssignments.assigner',
        ]));
    }

    public function assignCoverDesigners(Request $request, Submission $submission)
    {
        $this->authorizeAdminOrOperator($request);

        $data = $request->validate([
            'cover_designer_ids' => 'nullable|array',
            'cover_designer_ids.*' => 'integer|exists:users,id',
            'note' => 'nullable|string',
        ]);

        $designerIds = collect($data['cover_designer_ids'] ?? [])->unique()->values();

        if ($designerIds->isNotEmpty()) {
            $validDesigners = User::with('role')->whereIn('id', $designerIds)->get();
            abort_if($validDesigners->count() !== $designerIds->count(), 422, 'Desainer Cover tidak valid.');
            abort_if($validDesigners->contains(fn ($user) => $user->role?->name !== 'Desain Cover'), 422, 'Role harus Desain Cover.');
        }

        SubmissionCoverAssignment::where('submission_id', $submission->id)->delete();

        foreach ($designerIds as $designerId) {
            SubmissionCoverAssignment::create([
                'submission_id' => $submission->id,
                'cover_designer_id' => $designerId,
                'assigned_by' => $request->user()->id,
                'note' => $data['note'] ?? null,
            ]);
        }

        if ($submission->status === 'submitted' && $designerIds->isNotEmpty()) {
            $submission->update(['status' => 'under_review']);
        }

        return response()->json($submission->fresh()->load([
            'category',
            'reviews.editor',
            'revisions.user',
            'editorAssignments.editor',
            'editorAssignments.assigner',
            'layouterAssignments.layouter',
            'layouterAssignments.assigner',
            'coverAssignments.designer',
            'coverAssignments.assigner',
        ]));
    }

    public function uploadRevision(Request $request, Submission $submission)
    {
        $this->authorizeAdminOrOperator($request);

        $data = $request->validate([
            'revision_file' => 'required|mimes:pdf,doc,docx|max:51200',
            'note' => 'nullable|string',
        ]);

        $filePath = $request->file('revision_file')->store('submissions/revisions', 'public');

        $revision = $submission->revisions()->create([
            'user_id' => $request->user()->id,
            'round' => $submission->current_round ?: 1,
            'revision_file' => $filePath,
            'note' => $data['note'] ?? 'Unggahan revisi naskah oleh Admin',
        ]);

        $latestVersion = $submission->manuscriptVersions()->max('version_number') ?: 1;
        $submission->manuscriptVersions()->create([
            'version_number' => $latestVersion + 1,
            'round' => $submission->current_round ?: 1,
            'file_path' => $filePath,
            'uploaded_by' => $request->user()->id,
            'note' => $data['note'] ?? 'Revisi naskah (Admin)',
        ]);

        return response()->json([
            'message' => 'Berkas revisi naskah berhasil diunggah.',
            'submission' => $submission->fresh()->load([
                'category',
                'manuscriptVersions.user.role',
                'editorEvaluations.editor',
                'editorialDecisions.decider',
                'editorAssignments.editor',
                'editorAssignments.assigner',
                'layouterAssignments.layouter',
                'layouterAssignments.assigner',
                'revisions.user.role',
                'reviews',
            ]),
            'revision' => $revision->load('user.role'),
        ], 201);
    }

    private function authorizeAdminOrOperator(Request $request): void
    {
        abort_if(!in_array($request->user()->role?->name, ['Admin', 'Operator'], true), 403);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 2;

        while (Submission::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    public function reviewCycles(Submission $submission)
    {
        $submission->load([
            'editorEvaluations.editor.role',
            'editorialDecisions.decider.role',
            'revisions.user.role',
            'manuscriptVersions.user.role',
            'editorAssignments.editor',
        ]);

        $maxRound = max(
            $submission->current_round ?: 1,
            $submission->editorEvaluations->max('round') ?: 1,
            $submission->editorialDecisions->max('round') ?: 1
        );

        $cycles = [];
        for ($r = 1; $r <= $maxRound; $r++) {
            $decision = $submission->editorialDecisions->firstWhere('round', $r);
            $cycles[] = [
                'round' => $r,
                'evaluations' => $submission->editorEvaluations->where('round', $r)->values(),
                'decision' => $decision,
                'revisions' => $submission->revisions->where('round', $r)->values(),
                'manuscript_versions' => $submission->manuscriptVersions->where('round', $r)->values(),
                'status' => $decision ? $decision->decision : ($r === ($submission->current_round ?: 1) ? 'open' : 'unknown'),
            ];
        }

        return response()->json([
            'submission' => $submission->only(['id', 'title', 'slug', 'author_name', 'status', 'current_round', 'submitted_at', 'reviewed_at']),
            'editor_assignments' => $submission->editorAssignments->load('editor'),
            'cycles' => $cycles,
        ]);
    }

    public function publish(Request $request, Submission $submission)
    {
        $this->authorizeAdminOrOperator($request);

        $input = $request->validate([
            'title' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:book_categories,id',
            'author_name' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'publisher' => 'nullable|string|max:255',
            'pages' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($submission->status === 'published') {
            $existingBook = \App\Models\Book::where('title', $submission->title)->first();
            return response()->json([
                'message' => 'Naskah sudah diterbitkan sebelumnya.',
                'book' => $existingBook,
                'submission' => $submission->fresh()->load([
                    'category',
                    'manuscriptVersions.user.role',
                    'editorEvaluations.editor',
                    'editorialDecisions.decider',
                    'editorAssignments.editor',
                    'layouterAssignments.layouter',
                    'revisions.user.role',
                ]),
            ]);
        }

        $authorName = $input['author_name'] ?? ($submission->author_name ?: ($submission->user?->name ?: 'Penulis'));
        $authorId = null;

        if ($submission->user_id) {
            $user = \App\Models\User::with('author')->find($submission->user_id);
            if ($user && $user->author) {
                $authorId = $user->author->id;
            }
        }

        if (!$authorId) {
            $author = \App\Models\Author::firstOrCreate(
                ['name' => $authorName],
                [
                    'user_id' => $submission->user_id,
                    'slug' => Str::slug($authorName) . '-' . rand(100, 999),
                    'email' => $submission->email ?: 'author@uiidalwapress.com',
                    'phone' => $submission->phone,
                ]
            );
            $authorId = $author->id;
        }

        $latestLayouterFile = $submission->revisions()
            ->where(function ($query) {
                $query->whereHas('user.role', fn ($q) => $q->where('name', 'Layouter'))
                    ->orWhere('note', 'like', '%layout%');
            })
            ->latest()
            ->value('revision_file');

        if (!$latestLayouterFile) {
            $latestLayouterFile = $submission->revisions()->latest()->value('revision_file')
                ?: $submission->manuscript_file;
        }

        $latestCoverFile = $submission->revisions()
            ->where(function ($query) {
                $query->whereHas('user.role', fn ($q) => $q->where('name', 'Desain Cover'))
                    ->orWhere('note', 'like', '%cover%')
                    ->orWhere('note', 'like', '%sampul%');
            })
            ->latest()
            ->value('revision_file');

        if (!$latestCoverFile) {
            $latestCoverFile = $submission->cover_file;
        }

        $bookTitle = $input['title'] ?? $submission->title;
        $baseSlug = Str::slug($bookTitle);
        $bookSlug = $baseSlug;
        $counter = 2;
        while (\App\Models\Book::withTrashed()->where('slug', $bookSlug)->exists()) {
            $bookSlug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $book = \App\Models\Book::create([
            'category_id' => $input['category_id'] ?? $submission->category_id,
            'author_id' => $authorId,
            'submission_id' => $submission->id,
            'title' => $bookTitle,
            'slug' => $bookSlug,
            'isbn' => $input['isbn'] ?? null,
            'publisher' => $input['publisher'] ?? 'UII Dalwa Press',
            'year' => $input['year'] ?? date('Y'),
            'pages' => $input['pages'] ?? null,
            'size' => $input['size'] ?? null,
            'language' => $input['language'] ?? 'Indonesia',
            'cover' => $latestCoverFile,
            'full_file' => $latestLayouterFile,
            'description' => $input['description'] ?? $submission->description,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $submission->update([
            'status' => 'published',
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Naskah berhasil diterbitkan menjadi data Buku!',
            'book' => $book->load(['category', 'author']),
            'submission' => $submission->fresh()->load([
                'category',
                'manuscriptVersions.user.role',
                'editorEvaluations.editor',
                'editorialDecisions.decider',
                'editorAssignments.editor',
                'layouterAssignments.layouter',
                'revisions.user.role',
            ]),
        ]);
    }

    public function deleteRevision(Request $request, Submission $submission, $revisionId)
    {
        $this->authorizeAdminOrOperator($request);

        if ($revisionId === 'main-manuscript') {
            if ($submission->manuscript_file && Storage::disk('public')->exists($submission->manuscript_file)) {
                Storage::disk('public')->delete($submission->manuscript_file);
            }
            $submission->update(['manuscript_file' => null]);
        } elseif ($revisionId === 'main-cover') {
            if ($submission->cover_file && Storage::disk('public')->exists($submission->cover_file)) {
                Storage::disk('public')->delete($submission->cover_file);
            }
            $submission->update(['cover_file' => null]);
        } else {
            $realId = str_starts_with((string) $revisionId, 'rev-') ? (int) str_replace('rev-', '', $revisionId) : (int) $revisionId;
            $revision = SubmissionRevision::where('submission_id', $submission->id)->find($realId);

            if ($revision) {
                if ($revision->revision_file && Storage::disk('public')->exists($revision->revision_file)) {
                    Storage::disk('public')->delete($revision->revision_file);
                }
                $revision->delete();
            } else {
                return response()->json(['message' => 'Berkas revisi tidak ditemukan.'], 404);
            }
        }

        return response()->json([
            'message' => 'Berkas berhasil dihapus.',
            'submission' => $submission->fresh()->load([
                'category',
                'manuscriptVersions.user.role',
                'editorEvaluations.editor',
                'editorialDecisions.decider',
                'editorAssignments.editor',
                'layouterAssignments.layouter',
                'coverAssignments.designer',
                'revisions.user.role',
            ]),
        ]);
    }
}

