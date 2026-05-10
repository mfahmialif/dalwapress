<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\SubmissionEditorAssignment;
use App\Models\SubmissionReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Submission::query()->with(['category', 'reviews', 'editorAssignments.editor']);

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
        return response()->json($submission->load(['category', 'reviews.editor', 'revisions', 'editorAssignments.editor', 'editorAssignments.assigner']));
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
        ]);

        if ($data['title'] !== $submission->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $submission->id);
        }

        if (in_array($data['status'], ['revision', 'accepted', 'rejected', 'published'], true) && !$submission->reviewed_at) {
            $data['reviewed_at'] = now();
        }

        $submission->update($data);

        return response()->json($submission->load(['category', 'reviews', 'editorAssignments.editor']));
    }

    public function destroy(Submission $submission)
    {
        foreach (['manuscript_file', 'cover_file'] as $field) {
            if ($submission->{$field}) Storage::disk('public')->delete($submission->{$field});
        }

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

    private function authorizeAdminOrOperator(Request $request): void
    {
        abort_if(!in_array($request->user()->role?->name, ['Admin', 'Operator'], true), 403);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
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
}
