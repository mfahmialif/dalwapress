<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\SubmissionEditorEvaluation;
use App\Models\SubmissionEditorialDecision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                'published' => (clone $query)->where('status', 'published')->count(),
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
        $this->authorizeEditor($request);
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

    public function reviewCycles(Request $request, Submission $submission)
    {
        $this->authorizeEditor($request);
        $this->authorizeAssigned($request, $submission);

        $submission->load([
            'editorEvaluations.editor.role',
            'editorialDecisions.decider.role',
            'revisions.user.role',
            'manuscriptVersions.user.role',
            'editorAssignments.editor.role',
        ]);

        $currentRound = (int) ($submission->current_round ?: 1);
        $maxRound = max(
            $currentRound,
            (int) ($submission->editorEvaluations->max('round') ?: 1),
            (int) ($submission->editorialDecisions->max('round') ?: 1)
        );

        $cycles = [];
        for ($round = 1; $round <= $maxRound; $round++) {
            $decision = $submission->editorialDecisions->firstWhere('round', $round);

            $cycles[] = [
                'round' => $round,
                'evaluations' => $submission->editorEvaluations->where('round', $round)->values(),
                'decision' => $decision,
                'revisions' => $submission->revisions->where('round', $round)->values(),
                'manuscript_versions' => $submission->manuscriptVersions->where('round', $round)->values(),
                'status' => $decision?->decision ?: ($round === $currentRound ? 'open' : 'unknown'),
            ];
        }

        return response()->json([
            'submission' => $submission->only([
                'id',
                'title',
                'slug',
                'author_name',
                'status',
                'current_round',
                'submitted_at',
                'reviewed_at',
            ]),
            'editor_assignments' => $submission->editorAssignments->values(),
            'cycles' => $cycles,
        ]);
    }

    public function storeEvaluation(Request $request, Submission $submission)
    {
        $this->authorizeEditorOrAdmin($request);
        $this->authorizeAssignedOrAdmin($request, $submission);

        $data = $request->validate([
            'recommendation' => 'required|in:accept,revision,reject',
            'notes' => 'required|string',
            'editor_id' => 'nullable|exists:users,id',
        ]);

        $user = $request->user();
        $targetEditorId = $user->id;

        // If Admin/Operator and specific editor_id provided, allow acting on behalf of that editor
        if (in_array($user->role?->name, ['Admin', 'Operator'], true) && !empty($data['editor_id'])) {
            $targetEditorId = (int) $data['editor_id'];
        }

        $round = $submission->current_round ?: 1;

        $evaluation = SubmissionEditorEvaluation::updateOrCreate(
            [
                'submission_id' => $submission->id,
                'round' => $round,
                'editor_id' => $targetEditorId,
            ],
            [
                'recommendation' => $data['recommendation'],
                'notes' => $data['notes'],
                'submitted_at' => now(),
            ]
        );

        if ($submission->status === 'submitted') {
            $submission->update(['status' => 'under_review']);
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
            'evaluation' => $evaluation->load('editor'),
        ], 200);
    }

    public function storeFinalDecision(Request $request, Submission $submission)
    {
        $user = $request->user();
        $isAdmin = in_array($user->role?->name, ['Admin', 'Operator'], true);
        $isPrimary = $submission->editorAssignments()
            ->where('editor_id', $user->id)
            ->where('role', 'primary')
            ->exists();

        abort_if(!$isPrimary && !$isAdmin, 403, 'Hanya Primary Editor atau Admin yang dapat menetapkan Keputusan Editorial Final.');

        $data = $request->validate([
            'decision' => 'required|in:revision_required,accepted,rejected',
            'notes' => 'required|string',
        ]);

        $round = $submission->current_round ?: 1;

        DB::transaction(function () use ($submission, $data, $user, $round) {
            SubmissionEditorialDecision::updateOrCreate(
                [
                    'submission_id' => $submission->id,
                    'round' => $round,
                ],
                [
                    'decision' => $data['decision'],
                    'notes' => $data['notes'],
                    'decided_by' => $user->id,
                    'decided_at' => now(),
                ]
            );

            $newStatus = match ($data['decision']) {
                'revision_required' => 'revision',
                'accepted' => 'accepted',
                'rejected' => 'rejected',
            };

            $updateData = [
                'status' => $newStatus,
                'reviewed_at' => now(),
            ];

            // Auto-advance to next round when revision is required
            if ($data['decision'] === 'revision_required') {
                $updateData['current_round'] = $round + 1;
            }

            $submission->update($updateData);
        });

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
        ], 200);
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
        abort_if(!$submission->editorAssignments()->where('editor_id', $request->user()->id)->exists(), 403, 'Anda tidak ditugaskan sebagai editor naskah ini.');
    }

    private function authorizeEditor(Request $request): void
    {
        abort_if($request->user()->role?->name !== 'Editor', 403);
    }

    private function authorizeEditorOrAdmin(Request $request): void
    {
        abort_if(!in_array($request->user()->role?->name, ['Editor', 'Admin', 'Operator'], true), 403);
    }

    private function authorizeAssignedOrAdmin(Request $request, Submission $submission): void
    {
        if (in_array($request->user()->role?->name, ['Admin', 'Operator'], true)) {
            return;
        }
        $this->authorizeAssigned($request, $submission);
    }
}
