<?php

namespace Tests\Feature;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Certifications\Models\CertificationProvider;
use App\Domains\Curriculum\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LessonActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_their_lesson_complete_and_progress_updates(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();
        $lesson = $certification->lessons()->firstOrFail();

        $this->actingAs($user)
            ->post(route('lessons.completions.store', [
                'certificationSlug' => $certification->slug,
                'lesson' => $lesson->id,
            ]), [
                'confidence' => 4,
                'notes' => 'I can explain fact and dimension tables.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lesson_completions', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'confidence' => 4,
            'notes' => 'I can explain fact and dimension tables.',
        ]);

        $this->assertSame(33, $certification->refresh()->progress_percent);
    }

    public function test_marking_the_same_lesson_complete_updates_existing_completion(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();
        $lesson = $certification->lessons()->firstOrFail();

        $route = route('lessons.completions.store', [
            'certificationSlug' => $certification->slug,
            'lesson' => $lesson->id,
        ]);

        $this->actingAs($user)->post($route, ['confidence' => 2, 'notes' => 'First pass.']);
        $this->actingAs($user)->post($route, ['confidence' => 5, 'notes' => 'Reviewed and confident.']);

        $this->assertSame(1, $lesson->completions()->where('user_id', $user->id)->count());
        $this->assertDatabaseHas('lesson_completions', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'confidence' => 5,
            'notes' => 'Reviewed and confident.',
        ]);
    }

    public function test_user_can_save_a_lesson_note(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();
        $lesson = $certification->lessons()->firstOrFail();

        $this->actingAs($user)
            ->post(route('lessons.notes.store', [
                'certificationSlug' => $certification->slug,
                'lesson' => $lesson->id,
            ]), [
                'title' => 'Star schema summary',
                'body_markdown' => 'Facts measure events; dimensions describe them.',
                'is_favourite' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('notes', [
            'user_id' => $user->id,
            'noteable_type' => Lesson::class,
            'noteable_id' => $lesson->id,
            'title' => 'Star schema summary',
            'body_markdown' => 'Facts measure events; dimensions describe them.',
            'is_favourite' => true,
        ]);
    }

    public function test_user_cannot_write_activity_to_another_users_lesson(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $otherUser = User::query()->create([
            'name' => 'Other learner',
            'email' => 'other-activity@certpath.test',
            'password' => Hash::make('password'),
        ]);
        $provider = CertificationProvider::query()->firstOrCreate(
            ['slug' => 'other-activity-provider'],
            ['name' => 'Other Activity Provider']
        );
        $otherCertification = Certification::query()->create([
            'user_id' => $otherUser->id,
            'provider_id' => $provider->id,
            'name' => 'Other Activity Certification',
            'slug' => 'other-activity-certification',
            'exam_code' => 'OTHER-2',
            'track_type' => 'paid_professional',
            'status' => 'Planned',
            'priority' => 1,
            'is_primary' => false,
        ]);
        $otherLesson = $otherCertification->lessons()->create([
            'title' => 'Private lesson',
            'body_markdown' => 'Owned by someone else.',
        ]);

        $this->actingAs($owner)
            ->post(route('lessons.completions.store', [
                'certificationSlug' => $otherCertification->slug,
                'lesson' => $otherLesson->id,
            ]), ['confidence' => 3])
            ->assertNotFound();

        $this->assertDatabaseMissing('lesson_completions', [
            'user_id' => $owner->id,
            'lesson_id' => $otherLesson->id,
        ]);
    }
}
