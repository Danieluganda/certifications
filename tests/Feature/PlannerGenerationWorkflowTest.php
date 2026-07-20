<?php

namespace Tests\Feature;

use App\Domains\Planning\Models\StudyPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlannerGenerationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_availability_and_generation_controls_are_visible(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();

        $this->assertDatabaseHas('weekly_availabilities', [
            'user_id' => $user->id,
            'day_of_week' => 1,
            'start_time' => '19:00',
            'end_time' => '20:00',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.page', ['dashboardPage' => 'planner']))
            ->assertOk()
            ->assertSee('Availability')
            ->assertSee('Add availability')
            ->assertSee('Generate plan')
            ->assertSee('Generated plans');
    }

    public function test_user_can_save_weekly_availability(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();

        $this->actingAs($user)
            ->post(route('weekly-availabilities.store'), [
                'day_of_week' => 5,
                'start_time' => '18:30',
                'end_time' => '20:00',
                'is_active' => '1',
            ])
            ->assertRedirect(route('dashboard.page', ['dashboardPage' => 'planner']));

        $this->assertDatabaseHas('weekly_availabilities', [
            'user_id' => $user->id,
            'day_of_week' => 5,
            'start_time' => '18:30',
            'end_time' => '20:00',
            'is_active' => true,
        ]);
    }

    public function test_user_can_generate_study_plan_from_availability(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $availabilityCount = $user->weeklyAvailabilities()->where('is_active', true)->count();

        $this->actingAs($user)
            ->post(route('study-plans.generate'), [
                'starts_on' => '2026-07-20',
                'weeks' => 1,
            ])
            ->assertRedirect(route('dashboard.page', ['dashboardPage' => 'planner']));

        $plan = StudyPlan::query()
            ->where('user_id', $user->id)
            ->where('generated_by', 'system')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame($availabilityCount, $plan->sessions()->count());
        $this->assertDatabaseHas('study_sessions', [
            'user_id' => $user->id,
            'study_plan_id' => $plan->id,
            'status' => 'Pending',
        ]);
        $this->assertDatabaseHas('study_session_events', [
            'event_type' => 'generated',
        ]);
    }
}
