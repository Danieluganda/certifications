<?php

namespace App\Http\Controllers\Planning;

use App\Domains\Planning\Models\StudySession;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompleteStudySession extends Controller
{
    public function __invoke(Request $request, StudySession $studySession): RedirectResponse
    {
        if ($studySession->user_id !== $request->user()->id) {
            abort(404);
        }

        $data = $request->validate([
            'actual_minutes' => ['nullable', 'integer', 'min:1', 'max:480'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $studySession->forceFill([
            'status' => 'completed',
            'completed_at' => now(),
            'actual_minutes' => $data['actual_minutes'] ?? $studySession->planned_minutes,
            'notes' => $data['notes'] ?? $studySession->notes,
        ])->save();

        return redirect()
            ->route('dashboard.page', ['dashboardPage' => 'planner'])
            ->with('status', 'Study session completed.');
    }
}
