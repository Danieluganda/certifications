<?php

namespace Tests\Feature;

use App\Domains\Practice\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadinessWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_calculate_readiness_snapshot(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();
        $this->submitPerfectTopicQuiz($user, $certification);

        $this->actingAs($user)
            ->post(route('readiness.calculate', ['certificationSlug' => $certification->slug]))
            ->assertRedirect(route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'readiness']));

        $this->assertDatabaseHas('readiness_snapshots', [
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'status_label' => 'Foundation incomplete',
        ]);

        $this->assertGreaterThan(0, $certification->refresh()->readiness_percent);
        $this->assertDatabaseHas('topic_mastery', ['user_id' => $user->id]);
    }

    public function test_readiness_guard_conditions_are_stored(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();

        $this->actingAs($user)->post(route('readiness.calculate', ['certificationSlug' => $certification->slug]));

        $snapshot = $certification->readinessSnapshots()->firstOrFail();

        $this->assertArrayHasKey('readiness_at_least_75', $snapshot->guard_conditions);
        $this->assertFalse($snapshot->guard_conditions['required_labs_complete']);
    }

    private function submitPerfectTopicQuiz(User $user, $certification): void
    {
        $topic = $certification->topics()->where('name', 'Data modelling')->firstOrFail();

        $this->actingAs($user)->post(route('quiz-attempts.store', ['certificationSlug' => $certification->slug]), [
            'attempt_type' => 'topic',
            'topic_id' => $topic->id,
        ]);

        $attempt = QuizAttempt::query()->with('questions.version.options')->firstOrFail();
        $attemptQuestion = $attempt->questions->first();
        $correctOption = $attemptQuestion->version->options->firstWhere('is_correct', true);

        $this->actingAs($user)->post(route('quiz-attempts.submit', ['quizAttempt' => $attempt->id]), [
            'answers' => [$attemptQuestion->id => $correctOption->id],
        ]);
    }
}
