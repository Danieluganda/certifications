<?php

namespace App\Http\Controllers\Planning;

use App\Domains\Certifications\Models\Certification;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GenerateStudyPlan extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'starts_on' => ['required', 'date'],
            'weeks' => ['nullable', 'integer', 'min:1', 'max:4'],
        ]);

        $user = $request->user();
        $startsOn = Carbon::parse($data['starts_on'])->startOfWeek();
        $weeks = (int) ($data['weeks'] ?? 1);
        $endsOn = $startsOn->copy()->addWeeks($weeks)->subDay();

        $availability = $user->weeklyAvailabilities()
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        if ($availability->isEmpty()) {
            return back()->withErrors(['planner' => 'Add at least one active availability slot before generating a plan.']);
        }

        $certifications = Certification::query()
            ->with(['lessons.topic'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['Active', 'In progress', 'Planned'])
            ->orderByDesc('is_primary')
            ->orderBy('track_type')
            ->orderByDesc('priority')
            ->take(4)
            ->get();

        if ($certifications->isEmpty()) {
            return back()->withErrors(['planner' => 'Add at least one certification before generating a plan.']);
        }

        $plan = $user->studyPlans()->create([
            'name' => 'Generated plan '.$startsOn->format('M j').' - '.$endsOn->format('M j'),
            'starts_on' => $startsOn,
            'ends_on' => $endsOn,
            'status' => 'active',
            'generated_by' => 'system',
            'generation_context' => [
                'weeks' => $weeks,
                'availability_count' => $availability->count(),
                'strategy' => 'primary_first_then_supporting_tracks',
            ],
        ]);

        $created = 0;
        $slotIndex = 0;
        for ($week = 0; $week < $weeks; $week++) {
            foreach ($availability as $slot) {
                $certification = $certifications[$slotIndex % $certifications->count()];
                $lesson = $certification->lessons[$slotIndex % max($certification->lessons->count(), 1)] ?? null;
                $date = $startsOn->copy()
                    ->addWeeks($week)
                    ->addDays(((int) $slot->day_of_week) - 1);
                $scheduledStart = Carbon::parse($date->toDateString().' '.$slot->start_time);
                $scheduledEnd = Carbon::parse($date->toDateString().' '.$slot->end_time);
                $plannedMinutes = max(5, $scheduledStart->diffInMinutes($scheduledEnd));

                $session = $user->studySessions()->create([
                    'study_plan_id' => $plan->id,
                    'certification_id' => $certification->id,
                    'lesson_id' => $lesson?->id,
                    'topic_id' => $lesson?->topic_id,
                    'activity_type' => $lesson ? 'Lesson' : 'review',
                    'scheduled_for' => $scheduledStart,
                    'scheduled_start' => $scheduledStart,
                    'scheduled_end' => $scheduledEnd,
                    'planned_minutes' => $plannedMinutes,
                    'target_description' => $lesson
                        ? 'Complete '.$lesson->title.' and capture notes.'
                        : 'Review weak topics and update notes.',
                    'priority' => $certification->is_primary ? 1 : 3,
                    'priority_score' => $certification->is_primary ? 100 : 70,
                    'status' => 'Pending',
                ]);

                $session->tasks()->create([
                    'task_type' => $lesson ? 'lesson' : 'review',
                    'lesson_id' => $lesson?->id,
                    'topic_id' => $lesson?->topic_id,
                    'title' => $lesson ? 'Complete '.$lesson->title : 'Review weak topics and notes',
                    'target_value' => 1,
                    'position' => 1,
                ]);

                $session->events()->create([
                    'event_type' => 'generated',
                    'occurred_at' => now(),
                    'metadata' => ['study_plan_id' => $plan->id],
                ]);

                $created++;
                $slotIndex++;
            }
        }

        return redirect()
            ->route('dashboard.page', ['dashboardPage' => 'planner'])
            ->with('status', "Study plan generated with {$created} session(s).");
    }
}
