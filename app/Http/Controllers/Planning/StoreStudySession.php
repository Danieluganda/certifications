<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreStudySession extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'certification_id' => ['required', 'integer'],
            'lesson_id' => ['nullable', 'integer'],
            'activity_type' => ['required', Rule::in(['Lesson', 'quiz', 'review', 'lab', 'project'])],
            'scheduled_for' => ['required', 'date'],
            'planned_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $certification = $request->user()
            ->certifications()
            ->whereKey($data['certification_id'])
            ->firstOrFail();

        $lessonId = null;
        if (! empty($data['lesson_id'])) {
            $lessonId = $certification->lessons()
                ->whereKey($data['lesson_id'])
                ->firstOrFail()
                ->id;
        }

        $request->user()->studySessions()->create([
            'certification_id' => $certification->id,
            'lesson_id' => $lessonId,
            'activity_type' => $data['activity_type'],
            'scheduled_for' => $data['scheduled_for'],
            'planned_minutes' => $data['planned_minutes'],
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('dashboard.page', ['dashboardPage' => 'planner'])
            ->with('status', 'Study session scheduled.');
    }
}
