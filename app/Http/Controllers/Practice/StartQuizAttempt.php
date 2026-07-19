<?php

namespace App\Http\Controllers\Practice;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StartQuizAttempt extends Controller
{
    public function __invoke(Request $request, string $certificationSlug): RedirectResponse
    {
        $data = $request->validate([
            'attempt_type' => ['required', Rule::in(['quick', 'topic', 'domain', 'weakest', 'missed', 'practice', 'certification', 'mock'])],
            'domain_id' => ['nullable', 'integer'],
            'topic_id' => ['nullable', 'integer'],
            'question_count' => ['nullable', 'integer', 'min:1', 'max:100'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:240'],
        ]);

        $certification = $request->user()->certifications()->where('slug', $certificationSlug)->firstOrFail();
        Gate::authorize('view', $certification);

        $questionQuery = $certification->questions()
            ->with('currentVersion.options')
            ->where('status', 'active');

        $configuration = ['mode' => $data['attempt_type']];
        if (in_array($data['attempt_type'], ['topic', 'practice'], true) && ! empty($data['topic_id'])) {
            $topic = $certification->topics()->whereKey($data['topic_id'] ?? null)->firstOrFail();
            $questionQuery->where('topic_id', $topic->id);
            $configuration['topic_id'] = $topic->id;
            $configuration['topic_name'] = $topic->name;
        }

        if ($data['attempt_type'] === 'domain') {
            $domain = $certification->domains()->whereKey($data['domain_id'] ?? null)->firstOrFail();
            $questionQuery->where('domain_id', $domain->id);
            $configuration['domain_id'] = $domain->id;
            $configuration['domain_name'] = $domain->name;
        }

        if ($data['attempt_type'] === 'weakest') {
            $domainIdsWithQuestions = $certification->questions()
                ->where('status', 'active')
                ->pluck('domain_id')
                ->unique();

            $domain = $certification->domains()
                ->whereIn('id', $domainIdsWithQuestions)
                ->orderBy('mastery_percent')
                ->firstOrFail();
            $questionQuery->where('domain_id', $domain->id);
            $configuration['domain_id'] = $domain->id;
            $configuration['domain_name'] = $domain->name;
        }

        if ($data['attempt_type'] === 'missed') {
            $missedQuestionIds = $request->user()->quizAttempts()
                ->where('certification_id', $certification->id)
                ->whereIn('status', ['submitted', 'expired'])
                ->with('questions')
                ->get()
                ->flatMap->questions
                ->where('is_correct', false)
                ->pluck('question_id')
                ->unique()
                ->values();

            $questionQuery->whereIn('id', $missedQuestionIds);
            $configuration['missed_question_count'] = $missedQuestionIds->count();
        }

        $isCertificationMode = in_array($data['attempt_type'], ['certification', 'mock'], true);
        $defaultQuestionCount = match ($data['attempt_type']) {
            'quick' => 10,
            'certification', 'mock' => 20,
            default => 10,
        };

        $questions = $questionQuery->inRandomOrder()
            ->limit($data['question_count'] ?? $defaultQuestionCount)
            ->get();

        if ($questions->isEmpty()) {
            return back()->withErrors(['quiz' => 'No reviewed questions are available for this selection yet.']);
        }

        $duration = $isCertificationMode
            ? ($data['duration_minutes'] ?? 60)
            : null;

        $attempt = $request->user()->quizAttempts()->create([
            'certification_id' => $certification->id,
            'attempt_type' => $data['attempt_type'],
            'status' => 'In_progress',
            'started_at' => now(),
            'expires_at' => $duration ? now()->addMinutes($duration) : null,
            'total_questions' => $questions->count(),
            'configuration_snapshot' => $configuration + [
                'duration_minutes' => $duration,
                'question_count' => $questions->count(),
            ],
        ]);

        foreach ($questions as $position => $question) {
            $attempt->questions()->create([
                'question_id' => $question->id,
                'question_version_id' => $question->currentVersion->id,
                'position' => $position + 1,
            ]);
        }

        return redirect()->route('quiz-attempts.show', ['quizAttempt' => $attempt->id]);
    }
}
