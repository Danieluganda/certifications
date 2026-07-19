<?php

namespace App\Http\Controllers\Projects;

use App\Domains\Projects\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreProjectEvidence extends Controller
{
    public function __invoke(Request $request, Project $project): RedirectResponse
    {
        if ($project->user_id !== $request->user()->id) {
            abort(404);
        }

        $data = $request->validate([
            'evidence' => ['required', 'file', 'max:10240'],
            'description' => ['nullable', 'string', 'max:2000'],
            'review_notes' => ['nullable', 'string', 'max:5000'],
            'mark_completed' => ['nullable', 'boolean'],
        ]);

        $file = $data['evidence'];
        $path = $file->store('evidence/projects');

        $project->evidenceFiles()->create([
            'user_id' => $request->user()->id,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'description' => $data['description'] ?? null,
        ]);

        if ($request->boolean('mark_completed')) {
            $project->forceFill([
                'status' => 'Completed',
                'completed_at' => now(),
                'review_notes' => $data['review_notes'] ?? $project->review_notes,
            ])->save();
        }

        return back()->with('status', 'Project evidence saved.');
    }
}
