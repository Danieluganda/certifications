<?php

namespace App\Http\Controllers\Practice;

use App\Domains\Practice\Models\QuizAttempt;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubmitQuizAttempt extends Controller
{
    public function __invoke(Request $request, QuizAttempt $quizAttempt): RedirectResponse
    {
        if ($quizAttempt->user_id !== $request->user()->id) {
            abort(404);
        }

        if ($quizAttempt->status !== 'In_progress') {
            return back()->withErrors(['quiz' => 'This attempt has already been submitted.']);
        }

        $data = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'integer'],
        ]);

        $quizAttempt->load('questions.version.options', 'questions.question.domain');

        DB::transaction(function () use ($data, $quizAttempt): void {
            $correct = 0;
            $unanswered = 0;

            foreach ($quizAttempt->questions as $attemptQuestion) {
                $selectedId = $data['answers'][$attemptQuestion->id] ?? null;
                $selectedOption = $selectedId
                    ? $attemptQuestion->version->options->firstWhere('id', (int) $selectedId)
                    : null;

                if (! $selectedOption) {
                    $unanswered++;
                }

                $isCorrect = (bool) $selectedOption?->is_correct;
                $correct += $isCorrect ? 1 : 0;

                $attemptQuestion->answer()->updateOrCreate([], ['selected_option_id' => $selectedOption?->id]);
                $attemptQuestion->forceFill([
                    'points_awarded' => $isCorrect ? 1 : 0,
                    'is_correct' => $isCorrect,
                ])->save();
            }

            $total = max(1, $quizAttempt->total_questions);
            $incorrect = $total - $correct - $unanswered;
            $score = round(($correct / $total) * 100, 2);
            $expired = $quizAttempt->expires_at && now()->greaterThan($quizAttempt->expires_at);

            $quizAttempt->forceFill([
                'status' => $expired ? 'expired' : 'submitted',
                'submitted_at' => now(),
                'score_percent' => $score,
                'passed' => $score >= 70,
                'correct_count' => $correct,
                'incorrect_count' => $incorrect,
                'unanswered_count' => $unanswered,
                'time_used_seconds' => $quizAttempt->started_at->diffInSeconds(now()),
            ])->save();

            $quizAttempt->domainScores()->delete();
            $quizAttempt->questions->groupBy(fn ($attemptQuestion) => $attemptQuestion->question->domain_id)
                ->each(function ($questions, $domainId) use ($quizAttempt): void {
                    $domainCorrect = $questions->where('is_correct', true)->count();
                    $domainTotal = $questions->count();
                    $quizAttempt->domainScores()->create([
                        'domain_id' => $domainId,
                        'score_percent' => round(($domainCorrect / max(1, $domainTotal)) * 100, 2),
                        'correct_count' => $domainCorrect,
                        'total_count' => $domainTotal,
                    ]);
                });
        });

        return redirect()
            ->route('quiz-attempts.show', ['quizAttempt' => $quizAttempt->id])
            ->with('status', 'Quiz submitted.');
    }
}
