<?php

namespace Tests\Feature;

use App\Domains\Flashcards\Models\Flashcard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FlashcardWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_flashcard_for_a_topic(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();
        $topic = $certification->topics()->firstOrFail();

        $this->actingAs($user)
            ->post(route('flashcards.store', ['certificationSlug' => $certification->slug]), [
                'topic_id' => $topic->id,
                'front' => 'What does a fact table store?',
                'back' => 'Measurable events such as sales, enrolments, or transactions.',
                'source_type' => 'Manual',
                'source_reference' => 'PL-300 modelling notes',
            ])
            ->assertRedirect(route('certifications.show', ['certificationSlug' => $certification->slug]).'#flashcards');

        $this->assertDatabaseHas('flashcards', [
            'user_id' => $user->id,
            'topic_id' => $topic->id,
            'front' => 'What does a fact table store?',
            'status' => 'Active',
            'current_interval_days' => 0,
        ]);
    }

    public function test_user_can_review_a_flashcard_and_schedule_next_review(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $topic = $user->certifications()->where('slug', 'pl-300')->firstOrFail()->topics()->firstOrFail();
        $flashcard = Flashcard::query()->create([
            'user_id' => $user->id,
            'topic_id' => $topic->id,
            'front' => 'Dimension table?',
            'back' => 'Labels and descriptive context for facts.',
            'next_review_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('flashcards.reviews.store', ['flashcard' => $flashcard->id]), [
                'rating' => 'good',
                'confidence' => 4,
            ])
            ->assertRedirect();

        $flashcard->refresh();

        $this->assertSame(1, $flashcard->current_interval_days);
        $this->assertSame(1, $flashcard->review_count);
        $this->assertNotNull($flashcard->last_reviewed_at);
        $this->assertDatabaseHas('flashcard_reviews', [
            'user_id' => $user->id,
            'flashcard_id' => $flashcard->id,
            'rating' => 'good',
            'confidence' => 4,
            'previous_interval_days' => 0,
            'next_interval_days' => 1,
        ]);
    }

    public function test_incorrect_flashcard_review_returns_sooner_and_counts_lapse(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $topic = $user->certifications()->where('slug', 'pl-300')->firstOrFail()->topics()->firstOrFail();
        $flashcard = Flashcard::query()->create([
            'user_id' => $user->id,
            'topic_id' => $topic->id,
            'front' => 'Hard card',
            'back' => 'Review again today.',
            'current_interval_days' => 7,
            'next_review_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('flashcards.reviews.store', ['flashcard' => $flashcard->id]), [
                'rating' => 'again',
                'confidence' => 1,
            ])
            ->assertRedirect();

        $flashcard->refresh();

        $this->assertSame(0, $flashcard->current_interval_days);
        $this->assertSame(1, $flashcard->lapse_count);
    }

    public function test_user_cannot_review_another_users_flashcard(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $otherUser = User::query()->create([
            'name' => 'Other learner',
            'email' => 'other-flashcard@certpath.test',
            'password' => Hash::make('password'),
        ]);
        $topic = $owner->certifications()->where('slug', 'pl-300')->firstOrFail()->topics()->firstOrFail();
        $flashcard = Flashcard::query()->create([
            'user_id' => $otherUser->id,
            'topic_id' => $topic->id,
            'front' => 'Private card',
            'back' => 'Private answer',
            'next_review_at' => now(),
        ]);

        $this->actingAs($owner)
            ->post(route('flashcards.reviews.store', ['flashcard' => $flashcard->id]), [
                'rating' => 'good',
            ])
            ->assertNotFound();

        $this->assertSame(0, $flashcard->refresh()->review_count);
    }
}
