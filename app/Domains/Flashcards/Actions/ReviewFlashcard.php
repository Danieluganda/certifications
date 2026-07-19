<?php

namespace App\Domains\Flashcards\Actions;

use App\Domains\Flashcards\Models\Flashcard;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReviewFlashcard
{
    public function execute(User $user, Flashcard $flashcard, string $rating, ?int $confidence = null, ?int $responseTimeMs = null): void
    {
        if ($flashcard->user_id !== $user->id) {
            throw new InvalidArgumentException('The flashcard does not belong to this user.');
        }

        $previousInterval = $flashcard->current_interval_days;
        $nextInterval = $this->nextInterval($flashcard, $rating, $confidence);

        DB::transaction(function () use ($confidence, $flashcard, $nextInterval, $previousInterval, $rating, $responseTimeMs, $user): void {
            $flashcard->reviews()->create([
                'user_id' => $user->id,
                'rating' => $rating,
                'confidence' => $confidence,
                'previous_interval_days' => $previousInterval,
                'next_interval_days' => $nextInterval,
                'reviewed_at' => now(),
                'response_time_ms' => $responseTimeMs,
            ]);

            $easeFactor = (float) $flashcard->ease_factor;
            $easeFactor += match ($rating) {
                'again' => -0.35,
                'hard' => -0.15,
                'easy' => 0.20,
                default => 0.05,
            };

            $flashcard->forceFill([
                'current_interval_days' => $nextInterval,
                'ease_factor' => max(1.30, min(3.00, $easeFactor)),
                'next_review_at' => now()->addDays($nextInterval),
                'last_reviewed_at' => now(),
                'review_count' => $flashcard->review_count + 1,
                'lapse_count' => $flashcard->lapse_count + ($rating === 'again' ? 1 : 0),
            ])->save();
        });
    }

    private function nextInterval(Flashcard $flashcard, string $rating, ?int $confidence): int
    {
        $intervals = [0, 1, 3, 7, 14, 30];
        $current = $flashcard->current_interval_days;

        if ($rating === 'again') {
            return 0;
        }

        if ($rating === 'hard' || ($confidence !== null && $confidence <= 2)) {
            return $current <= 1 ? 1 : 3;
        }

        $index = array_search($current, $intervals, true);
        $index = $index === false ? 1 : $index;

        if ($rating === 'easy' && $confidence !== null && $confidence >= 4) {
            $index += 2;
        } else {
            $index += 1;
        }

        return $intervals[min($index, count($intervals) - 1)];
    }
}
