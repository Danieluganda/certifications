<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class StoreStudySession extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'certification_id' => ['required', 'integer'],
            'lesson_id' => ['nullable', 'integer'],
            'topic_id' => ['nullable', 'integer'],
            'activity_type' => ['required', Rule::in(['Lesson', 'quiz', 'review', 'lab', 'project'])],
            'scheduled_for' => ['required', 'date'],
            'scheduled_start' => ['nullable', 'date'],
            'scheduled_end' => ['nullable', 'date', 'after:scheduled_start'],
            'planned_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'target_description' => ['nullable', 'string', 'max:2000'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:5'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $certification = $request->user()
            ->certifications()
            ->whereKey($data['certification_id'])
            ->firstOrFail();

        $lessonId = null;
        if (! empty($data['lesson_id'])) {
            $lesson = $certification->lessons()
                ->whereKey($data['lesson_id'])
                ->firstOrFail();
            $lessonId = $lesson->id;
            $data['topic_id'] ??= $lesson->topic_id;
        }

        $topicId = null;
        if (! empty($data['topic_id'])) {
            $topicId = $certification->topics()
                ->whereKey($data['topic_id'])
                ->firstOrFail()
                ->id;
        }

        $scheduledStart = isset($data['scheduled_start'])
            ? Carbon::parse($data['scheduled_start'])
            : Carbon::parse($data['scheduled_for']);
        $scheduledEnd = isset($data['scheduled_end'])
            ? Carbon::parse($data['scheduled_end'])
            : $scheduledStart->copy()->addMinutes((int) $data['planned_minutes']);
        $targetDescription = $data['target_description'] ?? null;

        $session = $request->user()->studySessions()->create([
            'certification_id' => $certification->id,
            'lesson_id' => $lessonId,
            'topic_id' => $topicId,
            'activity_type' => $data['activity_type'],
            'scheduled_for' => $data['scheduled_for'],
            'scheduled_start' => $scheduledStart,
            'scheduled_end' => $scheduledEnd,
            'planned_minutes' => $data['planned_minutes'],
            'target_description' => $targetDescription,
            'priority' => $data['priority'] ?? 3,
            'notes' => $data['notes'] ?? null,
        ]);

        $session->tasks()->create([
            'task_type' => strtolower($data['activity_type']),
            'lesson_id' => $lessonId,
            'topic_id' => $topicId,
            'title' => $targetDescription ?: $this->defaultTaskTitle($data['activity_type']),
            'target_value' => $data['activity_type'] === 'quiz' ? 10 : 1,
            'position' => 1,
        ]);

        return redirect()
            ->route('dashboard.page', ['dashboardPage' => 'planner'])
            ->with('status', 'Study session scheduled.');
    }

    private function defaultTaskTitle(string $activityType): string
    {
        return match ($activityType) {
            'quiz' => 'Answer targeted practice questions',
            'review' => 'Review weak topics and notes',
            'lab' => 'Complete a practical lab step',
            'project' => 'Move one project milestone forward',
            default => 'Complete the planned lesson activity',
        };
    }
}
