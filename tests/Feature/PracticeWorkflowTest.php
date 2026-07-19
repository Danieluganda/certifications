<?php

namespace Tests\Feature;

use App\Domains\Practice\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PracticeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_import_creates_versioned_questions_from_lesson_quizzes(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();

        $this->assertGreaterThanOrEqual(3, $certification->questions()->count());
        $this->assertDatabaseHas('question_versions', ['prompt_markdown' => 'Which table usually stores measurable business events?']);
        $this->assertDatabaseHas('question_options', ['body_markdown' => 'Fact table', 'is_correct' => true]);
    }

    public function test_user_can_start_a_topic_quiz(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();
        $topic = $certification->topics()->where('name', 'Data modelling')->firstOrFail();

        $this->actingAs($user)
            ->post(route('quiz-attempts.store', ['certificationSlug' => $certification->slug]), [
                'attempt_type' => 'topic',
                'topic_id' => $topic->id,
                'question_count' => 5,
            ])
            ->assertRedirect();

        $attempt = QuizAttempt::query()->firstOrFail();

        $this->assertSame('topic', $attempt->attempt_type);
        $this->assertSame('In_progress', $attempt->status);
        $this->assertSame(1, $attempt->total_questions);
        $this->assertNull($attempt->expires_at);
        $this->assertSame(1, $attempt->questions()->count());
    }

    public function test_user_can_submit_a_topic_quiz_and_get_domain_scores(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();
        $topic = $certification->topics()->where('name', 'Data modelling')->firstOrFail();

        $this->actingAs($user)->post(route('quiz-attempts.store', ['certificationSlug' => $certification->slug]), [
            'attempt_type' => 'topic',
            'topic_id' => $topic->id,
        ]);

        $attempt = QuizAttempt::query()->with('questions.version.options')->firstOrFail();
        $attemptQuestion = $attempt->questions->first();
        $correctOption = $attemptQuestion->version->options->firstWhere('is_correct', true);

        $this->actingAs($user)
            ->post(route('quiz-attempts.submit', ['quizAttempt' => $attempt->id]), [
                'answers' => [$attemptQuestion->id => $correctOption->id],
            ])
            ->assertRedirect(route('quiz-attempts.show', ['quizAttempt' => $attempt->id]));

        $attempt->refresh();

        $this->assertSame('submitted', $attempt->status);
        $this->assertEquals(100.00, (float) $attempt->score_percent);
        $this->assertTrue($attempt->passed);
        $this->assertSame(1, $attempt->domainScores()->count());
    }

    public function test_user_can_start_a_timed_mock_exam(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();

        $this->actingAs($user)
            ->post(route('quiz-attempts.store', ['certificationSlug' => $certification->slug]), [
                'attempt_type' => 'mock',
                'question_count' => 2,
                'duration_minutes' => 45,
            ])
            ->assertRedirect();

        $attempt = QuizAttempt::query()->firstOrFail();

        $this->assertSame('mock', $attempt->attempt_type);
        $this->assertNotNull($attempt->expires_at);
        $this->assertSame(2, $attempt->total_questions);
    }

    public function test_user_cannot_open_another_users_attempt(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $other = User::factory()->create();
        $certification = $owner->certifications()->where('slug', 'pl-300')->firstOrFail();
        $attempt = QuizAttempt::query()->create([
            'user_id' => $other->id,
            'certification_id' => $certification->id,
            'attempt_type' => 'topic',
            'status' => 'In_progress',
            'started_at' => now(),
            'total_questions' => 0,
        ]);

        $this->actingAs($owner)
            ->get(route('quiz-attempts.show', ['quizAttempt' => $attempt->id]))
            ->assertNotFound();
    }
}
