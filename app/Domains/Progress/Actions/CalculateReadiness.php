<?php

namespace App\Domains\Progress\Actions;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Progress\Models\ReadinessSnapshot;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CalculateReadiness
{
    public function execute(User $user, Certification $certification): ReadinessSnapshot
    {
        if ($certification->user_id !== $user->id) {
            abort(404);
        }

        return DB::transaction(function () use ($certification, $user): ReadinessSnapshot {
            $topicMasteries = $certification->topics()->with(['lessons.completions', 'flashcards.reviews'])->get()
                ->map(function ($topic) use ($user) {
                    $quizComponent = $this->averageTopicQuizScore($user, $topic->id);
                    $reviewComponent = min(100, $topic->flashcards->flatMap->reviews->where('user_id', $user->id)->count() * 20);
                    $completedLessons = $topic->lessons->filter(fn ($lesson) => $lesson->completions->where('user_id', $user->id)->isNotEmpty())->count();
                    $lessonComponent = $topic->lessons->count() === 0 ? 0 : ($completedLessons / $topic->lessons->count()) * 100;
                    $confidenceValues = $topic->lessons->flatMap->completions->where('user_id', $user->id)->pluck('confidence')->filter();
                    $confidenceComponent = $confidenceValues->isEmpty() ? 0 : ($confidenceValues->avg() / 5) * 100;
                    $mastery = round(($quizComponent * .45) + ($reviewComponent * .15) + ($lessonComponent * .25) + ($confidenceComponent * .15), 2);

                    return $topic->mastery()->updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'mastery_percent' => $mastery,
                            'quiz_component' => $quizComponent,
                            'review_component' => $reviewComponent,
                            'lesson_component' => $lessonComponent,
                            'lab_component' => 0,
                            'confidence_component' => $confidenceComponent,
                            'calculated_at' => now(),
                            'calculation_version' => 'mvp-1',
                        ]
                    );
                });

            $topicQuizComponent = $this->averageAttemptScore($user, $certification, 'topic');
            $mockComponent = $this->averageAttemptScore($user, $certification, 'mock');
            $domainMasteryComponent = $topicMasteries->isEmpty() ? 0 : (float) $topicMasteries->avg('mastery_percent');
            $projectComponent = $certification->projects()->where('status', 'Completed')->exists() ? 100 : 0;
            $revisionComponent = min(100, $certification->studySessions()->where('user_id', $user->id)->where('status', 'completed')->count() * 25);
            $labComponent = 0;

            $readiness = round(
                ($topicQuizComponent * .25) +
                ($mockComponent * .30) +
                ($domainMasteryComponent * .15) +
                ($labComponent * .10) +
                ($projectComponent * .10) +
                ($revisionComponent * .10),
                2
            );

            $weakDomains = $this->weakDomains($user, $certification);
            $recentPassingMocks = $certification->quizAttempts()
                ->where('user_id', $user->id)
                ->where('attempt_type', 'mock')
                ->where('status', 'submitted')
                ->latest('submitted_at')
                ->take(3)
                ->get()
                ->filter(fn ($attempt) => (float) $attempt->score_percent >= 70)
                ->count();

            $guards = [
                'readiness_at_least_75' => $readiness >= 75,
                'three_recent_passing_mocks' => $recentPassingMocks >= 3,
                'no_major_domain_below_70' => $weakDomains->isEmpty(),
                'project_complete' => $projectComponent === 100,
                'required_labs_complete' => false,
            ];

            $snapshot = $certification->readinessSnapshots()->create([
                'user_id' => $user->id,
                'readiness_percent' => $readiness,
                'status_label' => $this->label($readiness),
                'topic_quiz_component' => $topicQuizComponent,
                'mock_exam_component' => $mockComponent,
                'domain_mastery_component' => $domainMasteryComponent,
                'lab_component' => $labComponent,
                'project_component' => $projectComponent,
                'revision_component' => $revisionComponent,
                'guard_conditions' => $guards,
                'calculated_at' => now(),
                'calculation_version' => 'mvp-1',
            ]);

            $certification->forceFill(['readiness_percent' => (int) round($readiness)])->save();

            return $snapshot;
        });
    }

    private function averageAttemptScore(User $user, Certification $certification, string $type): float
    {
        return (float) ($certification->quizAttempts()
            ->where('user_id', $user->id)
            ->where('attempt_type', $type)
            ->where('status', 'submitted')
            ->avg('score_percent') ?? 0);
    }

    private function averageTopicQuizScore(User $user, int $topicId): float
    {
        return (float) (DB::table('attempt_questions')
            ->join('quiz_attempts', 'quiz_attempts.id', '=', 'attempt_questions.quiz_attempt_id')
            ->join('questions', 'questions.id', '=', 'attempt_questions.question_id')
            ->where('quiz_attempts.user_id', $user->id)
            ->where('quiz_attempts.status', 'submitted')
            ->where('questions.topic_id', $topicId)
            ->avg('attempt_questions.points_awarded') ?? 0) * 100;
    }

    private function weakDomains(User $user, Certification $certification): Collection
    {
        return $certification->domains()->get()->filter(function ($domain) use ($user): bool {
            $score = DB::table('attempt_domain_scores')
                ->join('quiz_attempts', 'quiz_attempts.id', '=', 'attempt_domain_scores.quiz_attempt_id')
                ->where('quiz_attempts.user_id', $user->id)
                ->where('attempt_domain_scores.domain_id', $domain->id)
                ->avg('attempt_domain_scores.score_percent');

            return $score !== null && $score < 70;
        })->values();
    }

    private function label(float $readiness): string
    {
        return match (true) {
            $readiness >= 85 => 'Strongly prepared',
            $readiness >= 75 => 'Exam ready',
            $readiness >= 65 => 'Almost ready',
            $readiness >= 50 => 'Developing',
            default => 'Foundation incomplete',
        };
    }
}
