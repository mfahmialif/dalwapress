<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\SubmissionRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LayouterPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $this->authorizeLayouter($request);
        $query = $this->assignedSubmissions($request);

        return response()->json([
            'stats' => [
                'total' => (clone $query)->count(),
                'submitted' => (clone $query)->where('status', 'submitted')->count(),
                'under_review' => (clone $query)->where('status', 'under_review')->count(),
                'revision' => (clone $query)->where('status', 'revision')->count(),
                'accepted' => (clone $query)->where('status', 'accepted')->count(),
                'rejected' => (clone $query)->where('status', 'rejected')->count(),
                'published' => (clone $query)->where('status', 'published')->count(),
            ],
            'latest_submissions' => (clone $query)
                ->with(['category', 'layouterAssignments.layouter'])
                ->latest('submitted_at')
                ->take(5)
                ->get(),
        ]);
    }

    public function submissions(Request $request)
    {
        $this->authorizeLayouter($request);
        $query = $this->assignedSubmissions($request)->with([
            'category',
            'manuscriptVersions.user.role',
            'editorEvaluations.editor.role',
            'editorialDecisions.decider.role',
            'editorAssignments.editor.role',
            'layouterAssignments.layouter.role',
            'coverAssignments.designer.role',
            'revisions.user.role',
            'reviews.editor.role',
        ]);

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
        $this->authorizeLayouter($request);
        $this->authorizeAssigned($request, $submission);

        return response()->json($submission->load([
            'category',
            'manuscriptVersions.user.role',
            'editorEvaluations.editor.role',
            'editorialDecisions.decider.role',
            'editorAssignments.editor.role',
            'layouterAssignments.layouter.role',
            'coverAssignments.designer.role',
            'revisions.user.role',
            'reviews.editor.role',
        ]));
    }

    public function uploadLayout(Request $request, Submission $submission)
    {
        $this->authorizeLayouterOrAdmin($request);
        $this->authorizeAssignedOrAdmin($request, $submission);

        $data = $request->validate([
            'revision_file' => 'required|mimes:pdf,doc,docx|max:51200',
            'note' => 'nullable|string',
            'layouter_id' => 'nullable|exists:users,id',
        ]);

        $userId = $request->user()->id;
        if (in_array($request->user()->role?->name, ['Admin', 'Operator'], true) && !empty($data['layouter_id'])) {
            $userId = (int) $data['layouter_id'];
        }

        $existingRevision = $submission->revisions()
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('user.role', fn ($q) => $q->where('name', 'Layouter'))
                    ->orWhere('note', 'like', '%layout%');
            })
            ->latest()
            ->first();

        $newFilePath = $request->file('revision_file')->store('submissions/revisions', 'public');
        $note = $data['note'] ?? 'Hasil layout naskah oleh layouter';

        if ($existingRevision) {
            if ($existingRevision->revision_file && Storage::disk('public')->exists($existingRevision->revision_file)) {
                Storage::disk('public')->delete($existingRevision->revision_file);
            }
            $existingRevision->update([
                'user_id' => $userId,
                'revision_file' => $newFilePath,
                'note' => $note,
            ]);
            $revision = $existingRevision;
        } else {
            $revision = SubmissionRevision::create([
                'submission_id' => $submission->id,
                'user_id' => $userId,
                'revision_file' => $newFilePath,
                'note' => $note,
            ]);
        }

        return response()->json([
            'submission' => $submission->fresh()->load([
                'category',
                'manuscriptVersions.user.role',
                'editorEvaluations.editor.role',
                'editorialDecisions.decider.role',
                'editorAssignments.editor.role',
                'layouterAssignments.layouter.role',
                'coverAssignments.designer.role',
                'revisions.user.role',
                'reviews.editor.role',
            ]),
            'revision' => $revision->load('user'),
        ], 200);
    }

    public function profile(Request $request)
    {
        $this->authorizeLayouter($request);

        return response()->json($request->user()->load('role:id,name'));
    }

    private function assignedSubmissions(Request $request)
    {
        return Submission::query()
            ->whereHas('layouterAssignments', fn ($query) => $query->where('layouter_id', $request->user()->id));
    }

    private function authorizeAssigned(Request $request, Submission $submission): void
    {
        abort_if(!$submission->layouterAssignments()->where('layouter_id', $request->user()->id)->exists(), 403);
    }

    private function authorizeLayouter(Request $request): void
    {
        abort_if($request->user()->role?->name !== 'Layouter', 403);
    }

    private function authorizeLayouterOrAdmin(Request $request): void
    {
        abort_if(!in_array($request->user()->role?->name, ['Layouter', 'Admin', 'Operator'], true), 403);
    }

    private function authorizeAssignedOrAdmin(Request $request, Submission $submission): void
    {
        if (in_array($request->user()->role?->name, ['Admin', 'Operator'], true)) {
            return;
        }
        $this->authorizeAssigned($request, $submission);
    }
}
