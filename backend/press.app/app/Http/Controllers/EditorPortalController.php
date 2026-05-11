<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\SubmissionReview;
use Illuminate\Http\Request;

class EditorPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $this->authorizeEditor($request);
        $query = $this->assignedSubmissions($request);

        return response()->json([
            'stats' => [
                'total' => (clone $query)->count(),
                'submitted' => (clone $query)->where('status', 'submitted')->count(),
                'under_review' => (clone $query)->where('status', 'under_review')->count(),
                'revision' => (clone $query)->where('status', 'revision')->count(),
                'accepted' => (clone $query)->where('status', 'accepted')->count(),
                'rejected' => (clone $query)->where('status', 'rejected')->count(),
            ],
            'latest_submissions' => (clone $query)
                ->with(['category', 'editorAssignments.editor'])
                ->latest('submitted_at')
                ->take(5)
                ->get(),
        ]);
    }

    public function submissions(Request $request)
    {
        $this->authorizeEditor($request);
        $query = $this->assignedSubmissions($request)->with(['category', 'reviews.editor', 'editorAssignments.editor']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('author_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->latest('submitted_at')->paginate($request->input('per_page', 10));
    }

    public function show(Request $request, Submission $submission)
    {
        $this->authorizeEditor($request);
        $this->authorizeAssigned($request, $submission);

        return response()->json($submission->load(['category', 'reviews.editor', 'revisions', 'editorAssignments.editor']));
    }

    public function review(Request $request, Submission $submission)
    {
        $this->authorizeEditor($request);
        $this->authorizeAssigned($request, $submission);

        $data = $request->validate([
            'note' => 'nullable|string',
            'status' => 'required|in:revision,accepted,rejected',
        ]);

        $user = $request->user();
        $review = SubmissionReview::create([
            'submission_id' => $submission->id,
            'editor_id' => $user->id,
            'reviewer_name' => $user->name,
            'reviewer_email' => $user->email,
            'note' => $data['note'] ?? null,
            'status' => $data['status'],
            'created_at' => now(),
        ]);

        $submission->update([
            'status' => $data['status'],
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'submission' => $submission->fresh()->load(['category', 'reviews.editor', 'revisions', 'editorAssignments.editor']),
            'review' => $review->load('editor'),
        ], 201);
    }

    public function profile(Request $request)
    {
        $this->authorizeEditor($request);

        return response()->json($request->user()->load('role:id,name'));
    }

    private function assignedSubmissions(Request $request)
    {
        return Submission::query()
            ->whereHas('editorAssignments', fn ($query) => $query->where('editor_id', $request->user()->id));
    }

    private function authorizeAssigned(Request $request, Submission $submission): void
    {
        abort_if(!$submission->editorAssignments()->where('editor_id', $request->user()->id)->exists(), 403);
    }

    private function authorizeEditor(Request $request): void
    {
        abort_if($request->user()->role?->name !== 'Editor', 403);
    }
}
