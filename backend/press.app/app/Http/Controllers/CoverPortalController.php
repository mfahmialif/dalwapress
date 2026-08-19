<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\SubmissionRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CoverPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $this->authorizeCoverDesigner($request);
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
                ->with(['category', 'coverAssignments.designer'])
                ->latest('submitted_at')
                ->take(5)
                ->get(),
        ]);
    }

    public function submissions(Request $request)
    {
        $this->authorizeCoverDesigner($request);
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
        $this->authorizeCoverDesigner($request);
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

    public function uploadCover(Request $request, Submission $submission)
    {
        $this->authorizeCoverDesignerOrAdmin($request);
        $this->authorizeAssignedOrAdmin($request, $submission);

        $useInitialCover = $request->boolean('use_initial_cover');

        $data = $request->validate([
            'revision_file' => $useInitialCover ? 'nullable|file' : 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,psd,ai,zip,rar|max:51200',
            'note' => 'nullable|string',
            'cover_designer_id' => 'nullable|exists:users,id',
            'use_initial_cover' => 'nullable|boolean',
        ]);

        $userId = $request->user()->id;
        if (in_array($request->user()->role?->name, ['Admin', 'Operator'], true) && !empty($data['cover_designer_id'])) {
            $userId = (int) $data['cover_designer_id'];
        }

        $filePath = null;

        if ($useInitialCover) {
            abort_if(!$submission->cover_file, 422, 'Naskah ini tidak memiliki file cover awal.');

            $extension = pathinfo($submission->cover_file, PATHINFO_EXTENSION);
            $newPath = 'submissions/revisions/cover_' . Str::random(20) . ($extension ? ".{$extension}" : '');

            if (Storage::disk('public')->exists($submission->cover_file)) {
                Storage::disk('public')->copy($submission->cover_file, $newPath);
                $filePath = $newPath;
            } else {
                $filePath = $submission->cover_file;
            }
            $defaultNote = 'Menggunakan berkas sampul / cover dari pengajuan awal penulis.';
        } else {
            $filePath = $request->file('revision_file')->store('submissions/revisions', 'public');
            $defaultNote = 'Hasil desain sampul naskah oleh desainer cover';
        }

        $existingRevision = $submission->revisions()
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('user.role', fn ($q) => $q->where('name', 'Desain Cover'))
                    ->orWhere('note', 'like', '%cover%')
                    ->orWhere('note', 'like', '%sampul%');
            })
            ->latest()
            ->first();

        $note = !empty($data['note']) ? $data['note'] : $defaultNote;

        if ($existingRevision) {
            if ($existingRevision->revision_file && $existingRevision->revision_file !== $filePath && Storage::disk('public')->exists($existingRevision->revision_file)) {
                Storage::disk('public')->delete($existingRevision->revision_file);
            }
            $existingRevision->update([
                'user_id' => $userId,
                'revision_file' => $filePath,
                'note' => $note,
            ]);
            $revision = $existingRevision;
        } else {
            $revision = SubmissionRevision::create([
                'submission_id' => $submission->id,
                'user_id' => $userId,
                'revision_file' => $filePath,
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
        $this->authorizeCoverDesigner($request);

        return response()->json($request->user()->load('role:id,name'));
    }

    private function assignedSubmissions(Request $request)
    {
        return Submission::query()
            ->whereHas('coverAssignments', fn ($query) => $query->where('cover_designer_id', $request->user()->id));
    }

    private function authorizeAssigned(Request $request, Submission $submission): void
    {
        abort_if(!$submission->coverAssignments()->where('cover_designer_id', $request->user()->id)->exists(), 403);
    }

    private function authorizeAssignedOrAdmin(Request $request, Submission $submission): void
    {
        if (in_array($request->user()->role?->name, ['Admin', 'Operator'], true)) {
            return;
        }

        $this->authorizeAssigned($request, $submission);
    }

    private function authorizeCoverDesigner(Request $request): void
    {
        abort_if($request->user()->role?->name !== 'Desain Cover', 403);
    }

    private function authorizeCoverDesignerOrAdmin(Request $request): void
    {
        abort_if(!in_array($request->user()->role?->name, ['Desain Cover', 'Admin', 'Operator'], true), 403);
    }
}
