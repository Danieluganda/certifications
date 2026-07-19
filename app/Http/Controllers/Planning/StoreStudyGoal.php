<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreStudyGoal extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'certification_id' => ['nullable', 'integer'],
            'goal_period' => ['required', Rule::in(['daily', 'weekly', 'monthly', 'certification'])],
            'goal_type' => ['required', Rule::in([
                'study_minutes',
                'lessons_completed',
                'questions_answered',
                'flashcards_reviewed',
                'mock_exams_completed',
                'project_tasks_completed',
                'mastery_target',
                'readiness_target',
                'savings_target',
            ])],
            'target_value' => ['required', 'integer', 'min:1', 'max:1000000'],
            'unit' => ['required', 'string', 'max:40'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
        ]);

        $certificationId = null;
        if (! empty($data['certification_id'])) {
            $certificationId = $request->user()
                ->certifications()
                ->whereKey($data['certification_id'])
                ->firstOrFail()
                ->id;
        }

        $request->user()->studyGoals()->create([
            'certification_id' => $certificationId,
            'goal_period' => $data['goal_period'],
            'goal_type' => $data['goal_type'],
            'target_value' => $data['target_value'],
            'current_value' => 0,
            'unit' => $data['unit'],
            'starts_on' => $data['starts_on'],
            'ends_on' => $data['ends_on'],
        ]);

        return redirect()
            ->route('dashboard.page', ['dashboardPage' => 'planner'])
            ->with('status', 'Study goal added.');
    }
}
