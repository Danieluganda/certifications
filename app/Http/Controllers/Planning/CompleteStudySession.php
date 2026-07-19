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
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $studySession->forceFill([
            'status' => 'completed',
            'completed_at' => now(),
            'notes' => $data['notes'] ?? $studySession->notes,
        ])->save();

        return redirect()
            ->route('dashboard.page', ['dashboardPage' => 'planner'])
            ->with('status', 'Study session completed.');
    }
}
