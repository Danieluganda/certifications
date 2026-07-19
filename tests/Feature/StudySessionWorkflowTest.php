<?php

namespace Tests\Feature;

use App\Domains\Planning\Models\StudySession;
use App\Domains\Planning\Models\StudyGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudySessionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_schedule_a_study_session(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();
        $lesson = $certification->lessons()->firstOrFail();

        $this->actingAs($user)
            ->post(route('study-sessions.store'), [
                'certification_id' => $certification->id,
                'lesson_id' => $lesson->id,
                'activity_type' => 'Lesson',
                'scheduled_for' => '2026-07-20 18:00:00',
                'planned_minutes' => 45,
                'target_description' => 'Complete one lesson and answer 10 questions.',
                'priority' => 1,
                'notes' => 'Study model relationships.',
            ])
            ->assertRedirect(route('dashboard.page', ['dashboardPage' => 'planner']));

        $this->assertDatabaseHas('study_sessions', [
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'lesson_id' => $lesson->id,
            'topic_id' => $lesson->topic_id,
            'activity_type' => 'Lesson',
            'planned_minutes' => 45,
            'target_description' => 'Complete one lesson and answer 10 questions.',
            'priority' => 1,
            'status' => 'Pending',
            'notes' => 'Study model relationships.',
        ]);

        $this->assertDatabaseHas('session_tasks', [
            'lesson_id' => $lesson->id,
            'topic_id' => $lesson->topic_id,
            'task_type' => 'lesson',
            'title' => 'Complete one lesson and answer 10 questions.',
        ]);
    }

    public function test_user_can_schedule_a_study_session_without_optional_target_description(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();

        $this->actingAs($user)
            ->post(route('study-sessions.store'), [
                'certification_id' => $certification->id,
                'activity_type' => 'review',
                'scheduled_for' => '2026-07-20 18:00:00',
                'planned_minutes' => 30,
            ])
            ->assertRedirect(route('dashboard.page', ['dashboardPage' => 'planner']));

        $session = $user->studySessions()->latest('id')->firstOrFail();

        $this->assertNull($session->target_description);
        $this->assertDatabaseHas('session_tasks', [
            'study_session_id' => $session->id,
            'task_type' => 'review',
            'title' => 'Review weak topics and notes',
        ]);
    }

    public function test_user_can_complete_their_study_session(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();
        $session = $user->studySessions()->create([
            'certification_id' => $certification->id,
            'activity_type' => 'review',
            'scheduled_for' => '2026-07-20 19:00:00',
            'planned_minutes' => 30,
        ]);

        $this->actingAs($user)
            ->post(route('study-sessions.complete', ['studySession' => $session->id]), [
                'notes' => 'Reviewed DAX notes.',
            ])
            ->assertRedirect(route('dashboard.page', ['dashboardPage' => 'planner']));

        $session->refresh();

        $this->assertSame('completed', $session->status);
        $this->assertSame('Reviewed DAX notes.', $session->notes);
        $this->assertSame(30, $session->actual_minutes);
        $this->assertNotNull($session->completed_at);
    }

    public function test_user_can_create_a_study_goal(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();

        $this->actingAs($user)
            ->post(route('study-goals.store'), [
                'certification_id' => $certification->id,
                'goal_period' => 'weekly',
                'goal_type' => 'questions_answered',
                'target_value' => 120,
                'unit' => 'questions',
                'starts_on' => '2026-07-20',
                'ends_on' => '2026-07-26',
            ])
            ->assertRedirect(route('dashboard.page', ['dashboardPage' => 'planner']));

        $this->assertDatabaseHas('study_goals', [
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'goal_period' => 'weekly',
            'goal_type' => 'questions_answered',
            'target_value' => 120,
            'unit' => 'questions',
            'status' => 'active',
        ]);
    }

    public function test_planner_page_shows_first_class_planner_sections(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();

        $this->actingAs($user)
            ->get(route('dashboard.page', ['dashboardPage' => 'planner']))
            ->assertOk()
            ->assertSee("Continue today's plan", false)
            ->assertSee('Weekly workload')
            ->assertSee('Timetable')
            ->assertSee('Goals')
            ->assertSee('Project milestones')
            ->assertSee('Recommendations')
            ->assertSee('Schedule session')
            ->assertSee('Add goal');
    }

    public function test_user_cannot_complete_another_users_study_session(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $otherUser = User::query()->create([
            'name' => 'Other learner',
            'email' => 'other-session@certpath.test',
            'password' => Hash::make('password'),
        ]);
        $certification = $owner->certifications()->where('slug', 'pl-300')->firstOrFail();
        $session = StudySession::query()->create([
            'user_id' => $otherUser->id,
            'certification_id' => $certification->id,
            'activity_type' => 'review',
            'scheduled_for' => '2026-07-20 19:00:00',
            'planned_minutes' => 30,
        ]);

        $this->actingAs($owner)
            ->post(route('study-sessions.complete', ['studySession' => $session->id]))
            ->assertNotFound();

        $this->assertSame('Pending', $session->refresh()->status);
    }
}
