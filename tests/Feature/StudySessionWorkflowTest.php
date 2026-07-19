<?php

namespace Tests\Feature;

use App\Domains\Planning\Models\StudySession;
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

        $this->actingAs($user)
            ->post(route('study-sessions.store'), [
                'certification_id' => $certification->id,
                'activity_type' => 'Lesson',
                'scheduled_for' => '2026-07-20 18:00:00',
                'planned_minutes' => 45,
                'notes' => 'Study model relationships.',
            ])
            ->assertRedirect(route('dashboard').'#planner');

        $this->assertDatabaseHas('study_sessions', [
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'activity_type' => 'Lesson',
            'planned_minutes' => 45,
            'status' => 'Pending',
            'notes' => 'Study model relationships.',
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
            ->assertRedirect(route('dashboard').'#planner');

        $session->refresh();

        $this->assertSame('completed', $session->status);
        $this->assertSame('Reviewed DAX notes.', $session->notes);
        $this->assertNotNull($session->completed_at);
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
