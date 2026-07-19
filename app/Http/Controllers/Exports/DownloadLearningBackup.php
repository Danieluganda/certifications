<?php

namespace App\Http\Controllers\Exports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadLearningBackup extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $user = $request->user()->load([
            'profile',
            'certifications.provider',
            'certifications.domains.topics.mastery',
            'certifications.lessons.completions',
            'certifications.lessons.notes',
            'certifications.projects.evidenceFiles',
            'certifications.resources',
            'certifications.studySessions',
            'certifications.quizAttempts.domainScores',
            'certifications.quizAttempts.questions.answer.selectedOption',
            'certifications.readinessSnapshots',
            'certifications.savingsTransactions',
            'certifications.credentials',
            'flashcards.reviews',
            'flashcards.topic',
            'notes',
        ]);

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'product' => 'CertPath 123',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'profile' => $user->profile?->only([
                    'current_role',
                    'target_role',
                    'weekly_target_minutes',
                    'timezone',
                    'preferences',
                ]),
            ],
            'certifications' => $user->certifications->map(fn ($certification): array => [
                'name' => $certification->name,
                'exam_code' => $certification->exam_code,
                'provider' => $certification->provider?->name,
                'track_type' => $certification->track_type->value,
                'status' => $certification->status,
                'priority' => $certification->priority,
                'is_primary' => $certification->is_primary,
                'target_completion_date' => optional($certification->target_completion_date)->toDateString(),
                'weekly_minutes' => $certification->weekly_minutes,
                'readiness_percent' => $certification->readiness_percent,
                'progress_percent' => $certification->progress_percent,
                'exam_budget' => [
                    'currency' => $certification->exam_currency,
                    'target_amount_minor' => $certification->exam_target_amount_minor,
                    'saved_amount_minor' => $certification->exam_saved_amount_minor,
                    'transactions' => $certification->savingsTransactions->map->only([
                        'amount_minor',
                        'currency',
                        'transaction_type',
                        'transaction_date',
                        'notes',
                        'created_at',
                    ])->values(),
                ],
                'domains' => $certification->domains->map(fn ($domain): array => [
                    'name' => $domain->name,
                    'weight_percent' => $domain->weight_percent,
                    'mastery_percent' => $domain->mastery_percent,
                    'topics' => $domain->topics->map(fn ($topic): array => [
                        'name' => $topic->name,
                        'mastery_percent' => $topic->mastery_percent,
                        'mastery' => $topic->mastery?->only([
                            'mastery_percent',
                            'confidence_level',
                            'last_practiced_at',
                            'next_review_at',
                        ]),
                    ])->values(),
                ])->values(),
                'lessons' => $certification->lessons->map(fn ($lesson): array => [
                    'title' => $lesson->title,
                    'topic_name' => $lesson->topic_name,
                    'summary' => $lesson->summary,
                    'proof_task' => $lesson->proof_task,
                    'estimated_minutes' => $lesson->estimated_minutes,
                    'status' => $lesson->status,
                    'completions' => $lesson->completions->map->only([
                        'status',
                        'completed_at',
                        'time_spent_minutes',
                        'confidence_rating',
                    ])->values(),
                    'notes' => $lesson->notes->map->only([
                        'title',
                        'body_markdown',
                        'is_favourite',
                        'created_at',
                    ])->values(),
                ])->values(),
                'study_sessions' => $certification->studySessions->map->only([
                    'activity_type',
                    'scheduled_for',
                    'planned_minutes',
                    'actual_minutes',
                    'status',
                    'notes',
                    'completed_at',
                ])->values(),
                'projects' => $certification->projects->map(fn ($project): array => [
                    'title' => $project->title,
                    'business_problem' => $project->business_problem,
                    'scope_markdown' => $project->scope_markdown,
                    'repository_url' => $project->repository_url,
                    'demo_url' => $project->demo_url,
                    'status' => $project->status,
                    'target_date' => optional($project->target_date)->toDateString(),
                    'completed_at' => optional($project->completed_at)->toIso8601String(),
                    'review_notes' => $project->review_notes,
                    'evidence_files' => $project->evidenceFiles->map->only([
                        'original_name',
                        'mime_type',
                        'size_bytes',
                        'description',
                        'created_at',
                    ])->values(),
                ])->values(),
                'resources' => $certification->resources->map->only([
                    'title',
                    'resource_type',
                    'provider_name',
                    'url',
                    'trust_level',
                    'copyright_status',
                    'status',
                    'rating',
                    'notes',
                ])->values(),
                'quiz_attempts' => $certification->quizAttempts->map(fn ($attempt): array => [
                    'attempt_type' => $attempt->attempt_type,
                    'status' => $attempt->status,
                    'started_at' => optional($attempt->started_at)->toIso8601String(),
                    'submitted_at' => optional($attempt->submitted_at)->toIso8601String(),
                    'score_percent' => $attempt->score_percent,
                    'passed' => $attempt->passed,
                    'total_questions' => $attempt->total_questions,
                    'correct_count' => $attempt->correct_count,
                    'incorrect_count' => $attempt->incorrect_count,
                    'domain_scores' => $attempt->domainScores->map->only([
                        'domain_name',
                        'score_percent',
                        'correct_count',
                        'total_questions',
                    ])->values(),
                    'answers' => $attempt->questions->map(fn ($question): array => [
                        'position' => $question->position,
                        'is_correct' => $question->is_correct,
                        'points_awarded' => $question->points_awarded,
                        'selected_option' => $question->answer?->selectedOption?->option_text,
                    ])->values(),
                ])->values(),
                'readiness_snapshots' => $certification->readinessSnapshots->map->only([
                    'readiness_percent',
                    'progress_percent',
                    'weak_domains',
                    'recommendations',
                    'created_at',
                ])->values(),
                'credentials' => $certification->credentials->map->only([
                    'credential_name',
                    'provider_name',
                    'issue_date',
                    'expiry_date',
                    'credential_id',
                    'verification_url',
                    'linkedin_added',
                    'cv_added',
                    'renewal_reminder_date',
                ])->values(),
            ])->values(),
            'flashcards' => $user->flashcards->map(fn ($flashcard): array => [
                'topic' => $flashcard->topic?->name,
                'front' => $flashcard->front,
                'back' => $flashcard->back,
                'status' => $flashcard->status,
                'next_review_at' => optional($flashcard->next_review_at)->toIso8601String(),
                'last_reviewed_at' => optional($flashcard->last_reviewed_at)->toIso8601String(),
                'review_count' => $flashcard->review_count,
                'reviews' => $flashcard->reviews->map->only([
                    'quality',
                    'reviewed_at',
                    'response_time_seconds',
                ])->values(),
            ])->values(),
            'notes' => $user->notes->map->only([
                'title',
                'body_markdown',
                'is_favourite',
                'created_at',
                'updated_at',
            ])->values(),
        ];

        $filename = 'certpath-backup-'.now()->format('Y-m-d').'.json';

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, $filename, ['Content-Type' => 'application/json']);
    }
}
