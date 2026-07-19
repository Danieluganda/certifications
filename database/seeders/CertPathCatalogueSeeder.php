<?php

namespace Database\Seeders;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Certifications\Models\CertificationProvider;
use App\Domains\Curriculum\Models\CertificationDomain;
use App\Domains\Curriculum\Models\Lesson;
use App\Domains\Curriculum\Models\Topic;
use App\Domains\Practice\Models\Question;
use App\Domains\Planning\Models\PlannerRecommendation;
use App\Domains\Planning\Models\StudyGoal;
use App\Domains\Planning\Models\StudyStreak;
use App\Domains\Projects\Models\ProjectMilestone;
use App\Domains\Projects\Models\Project;
use App\Domains\Resources\Models\Resource;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CertPathCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        $payload = $this->mergePayloads([
            database_path('data/certifications/certpath-seed.json'),
            database_path('data/certifications/amendment-gis-knowledge-systems.json'),
            database_path('data/certifications/study-materials.json'),
        ]);

        $user = User::query()->firstOrCreate(
            ['email' => 'learner@certpath.test'],
            ['name' => 'Personal learner', 'password' => Hash::make('password')]
        );

        $user->profile()->updateOrCreate([], [
            'timezone' => $payload['profile']['timezone'] ?? 'Africa/Kampala',
            'weekly_target_minutes' => $payload['profile']['weeklyTargetMinutes'] ?? 0,
            'max_active_free_credentials' => 2,
        ]);

        foreach ($payload['certifications'] as $certificationData) {
            $provider = CertificationProvider::query()->firstOrCreate(
                ['slug' => Str::slug($certificationData['provider'])],
                ['name' => $certificationData['provider']]
            );

            $certification = Certification::query()->updateOrCreate(
                ['user_id' => $user->id, 'slug' => Str::slug($certificationData['id'])],
                [
                    'provider_id' => $provider->id,
                    'name' => $certificationData['name'],
                    'exam_code' => $certificationData['id'],
                    'track_type' => $certificationData['trackType'],
                    'status' => $certificationData['status'],
                    'priority' => $certificationData['priority'] ?? 3,
                    'is_primary' => $certificationData['isPrimary'] ?? false,
                    'target_completion_date' => $certificationData['targetDate'] ?? null,
                    'weekly_minutes' => $certificationData['weeklyMinutes'] ?? 0,
                    'readiness_percent' => $certificationData['readiness'] ?? 0,
                    'progress_percent' => $certificationData['progress'] ?? 0,
                    'exam_target_amount_minor' => isset($certificationData['examFund']['targetAmount'])
                        ? $certificationData['examFund']['targetAmount'] * 100
                        : null,
                    'exam_saved_amount_minor' => isset($certificationData['examFund']['savedAmount'])
                        ? $certificationData['examFund']['savedAmount'] * 100
                        : null,
                    'exam_currency' => $certificationData['examFund']['currency'] ?? null,
                    'metadata' => ['seed_source' => 'MATERIALS_AND_PROJECTS.md'],
                ]
            );

            $domainsByName = [];
            foreach (($certificationData['domains'] ?? []) as $position => $domainData) {
                $domain = CertificationDomain::query()->updateOrCreate(
                    ['certification_id' => $certification->id, 'name' => $domainData['name']],
                    [
                        'weight_percent' => $domainData['weight'] ?? null,
                        'mastery_percent' => $domainData['mastery'] ?? 0,
                        'position' => $position + 1,
                    ]
                );
                $domainsByName[$domain->name] = $domain;
            }

            foreach (($certificationData['lessons'] ?? []) as $position => $lessonData) {
                $domain = $domainsByName[$lessonData['domain']] ?? null;
                $topic = null;

                if ($domain && ! empty($lessonData['topic'])) {
                    $topic = Topic::query()->firstOrCreate(
                        [
                            'certification_id' => $certification->id,
                            'domain_id' => $domain->id,
                            'name' => $lessonData['topic'],
                        ],
                        ['position' => $position + 1]
                    );
                }

                $lesson = Lesson::query()->updateOrCreate(
                    ['certification_id' => $certification->id, 'external_id' => $lessonData['id']],
                    [
                        'domain_id' => $domain?->id,
                        'topic_id' => $topic?->id,
                        'topic_name' => $lessonData['topic'] ?? null,
                        'title' => $lessonData['title'],
                        'summary' => $lessonData['summary'] ?? null,
                        'body_markdown' => implode("\n\n", $lessonData['learn'] ?? []),
                        'example_markdown' => $this->formatExample($lessonData['example'] ?? []),
                        'exercise_markdown' => $this->formatExercise($lessonData['exercise'] ?? []),
                        'quiz_payload' => $lessonData['quiz'] ?? null,
                        'reference_payload' => $lessonData['reference'] ?? null,
                        'proof_task' => $lessonData['proofTask'] ?? null,
                        'estimated_minutes' => $lessonData['minutes'] ?? null,
                        'position' => $position + 1,
                    ]
                );

                if ($domain && $topic && ! empty($lessonData['quiz']['options'])) {
                    $question = Question::query()->updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'certification_id' => $certification->id,
                            'source_reference' => $lesson->external_id,
                        ],
                        [
                            'domain_id' => $domain->id,
                            'topic_id' => $topic->id,
                            'question_type' => 'single_choice',
                            'difficulty' => 'medium',
                            'status' => 'active',
                            'source_type' => 'lesson',
                            'current_version' => 1,
                        ]
                    );

                    $version = $question->versions()->updateOrCreate(
                        ['version_number' => 1],
                        [
                            'prompt_markdown' => $lessonData['quiz']['prompt'],
                            'explanation_markdown' => $lessonData['quiz']['explanation'] ?? null,
                            'answer_schema' => ['answer_index' => $lessonData['quiz']['answer'] ?? null],
                        ]
                    );

                    $version->options()->delete();
                    foreach ($lessonData['quiz']['options'] as $optionPosition => $option) {
                        $version->options()->create([
                            'option_key' => chr(65 + $optionPosition),
                            'body_markdown' => $option,
                            'is_correct' => $optionPosition === ($lessonData['quiz']['answer'] ?? -1),
                            'position' => $optionPosition + 1,
                        ]);
                    }
                }
            }
        }

        foreach ($payload['projects'] as $projectData) {
            $certification = Certification::query()
                ->where('user_id', $user->id)
                ->where('exam_code', $projectData['certificationId'])
                ->first();

            if (! $certification) {
                continue;
            }

            Project::query()->updateOrCreate(
                ['certification_id' => $certification->id, 'title' => $projectData['title']],
                [
                    'user_id' => $user->id,
                    'business_problem' => $projectData['businessProblem'],
                    'skills' => $projectData['skills'] ?? [],
                    'deliverables' => $projectData['deliverables'] ?? [],
                    'next_milestone' => $projectData['nextMilestone'] ?? null,
                    'status' => $projectData['status'] ?? 'Planned',
                ]
            );
        }

        foreach ($payload['resources'] as $resourceData) {
            $certification = Certification::query()
                ->where('user_id', $user->id)
                ->where('exam_code', $resourceData['certificationId'])
                ->first();

            if (! $certification) {
                continue;
            }

            Resource::query()->updateOrCreate(
                ['certification_id' => $certification->id, 'title' => $resourceData['title']],
                [
                    'user_id' => $user->id,
                    'resource_type' => $resourceData['resourceType'],
                    'provider_name' => $resourceData['provider'] ?? null,
                    'url' => $resourceData['officialUrl'] ?? null,
                    'trust_level' => $resourceData['trustLevel'] ?? 'personal',
                    'copyright_status' => $resourceData['copyrightStatus'] ?? 'personal_notes_allowed',
                    'status' => $resourceData['status'] ?? 'Not started',
                ]
            );
        }

        $this->seedPlannerFoundation($user);
    }

    private function seedPlannerFoundation(User $user): void
    {
        $primary = Certification::query()
            ->where('user_id', $user->id)
            ->where('is_primary', true)
            ->first();
        $powerAutomate = Certification::query()
            ->where('user_id', $user->id)
            ->where('exam_code', 'MS-APPLIED-POWER-AUTOMATE')
            ->first();

        StudyStreak::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'current_streak' => 3,
                'longest_streak' => 7,
                'last_qualified_date' => '2026-07-20',
                'freeze_count' => 1,
            ]
        );

        foreach ([
            [
                'certification' => $primary,
                'goal_period' => 'daily',
                'goal_type' => 'study_minutes',
                'target_value' => 60,
                'current_value' => 0,
                'unit' => 'minutes',
                'starts_on' => '2026-07-20',
                'ends_on' => '2026-07-20',
            ],
            [
                'certification' => $primary,
                'goal_period' => 'weekly',
                'goal_type' => 'questions_answered',
                'target_value' => 100,
                'current_value' => 0,
                'unit' => 'questions',
                'starts_on' => '2026-07-20',
                'ends_on' => '2026-07-26',
            ],
            [
                'certification' => $powerAutomate,
                'goal_period' => 'weekly',
                'goal_type' => 'lessons_completed',
                'target_value' => 1,
                'current_value' => 0,
                'unit' => 'lessons',
                'starts_on' => '2026-07-20',
                'ends_on' => '2026-07-26',
            ],
        ] as $goalData) {
            if (! $goalData['certification']) {
                continue;
            }

            StudyGoal::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'certification_id' => $goalData['certification']->id,
                    'goal_period' => $goalData['goal_period'],
                    'goal_type' => $goalData['goal_type'],
                    'starts_on' => $goalData['starts_on'],
                ],
                [
                    'target_value' => $goalData['target_value'],
                    'current_value' => $goalData['current_value'],
                    'unit' => $goalData['unit'],
                    'ends_on' => $goalData['ends_on'],
                    'status' => 'active',
                ]
            );
        }

        foreach ([
            [
                'certification' => $primary,
                'recommendation_type' => 'continue_today',
                'reason' => 'Keep momentum on the primary paid certification before adding optional work.',
                'recommended_date' => '2026-07-20',
                'duration_minutes' => 60,
                'priority' => 1,
            ],
            [
                'certification' => $powerAutomate,
                'recommendation_type' => 'supporting_free_credential',
                'reason' => 'Use a small automation credential to support the main study system and keep free progress active.',
                'recommended_date' => '2026-07-21',
                'duration_minutes' => 45,
                'priority' => 2,
            ],
        ] as $recommendationData) {
            if (! $recommendationData['certification']) {
                continue;
            }

            PlannerRecommendation::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'certification_id' => $recommendationData['certification']->id,
                    'recommendation_type' => $recommendationData['recommendation_type'],
                    'recommended_date' => $recommendationData['recommended_date'],
                ],
                [
                    'reason' => $recommendationData['reason'],
                    'duration_minutes' => $recommendationData['duration_minutes'],
                    'priority' => $recommendationData['priority'],
                ]
            );
        }

        $primaryProjects = Project::query()
            ->where('user_id', $user->id)
            ->where('certification_id', $primary?->id)
            ->take(2)
            ->get();

        foreach ($primaryProjects as $position => $project) {
            ProjectMilestone::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'project_id' => $project->id,
                    'title' => 'Define evidence checklist',
                ],
                [
                    'description' => 'Break the project into artifacts, screenshots, review notes, and proof tasks.',
                    'target_date' => '2026-07-26',
                    'status' => 'Planned',
                    'position' => $position + 1,
                ]
            );
        }
    }

    private function formatExample(array $example): string
    {
        return trim(($example['intro'] ?? '')."\n\n```text\n".($example['code'] ?? '')."\n```\n\n".($example['explanation'] ?? ''));
    }

    private function formatExercise(array $exercise): string
    {
        return trim(($exercise['prompt'] ?? '')."\n\n```text\n".($exercise['starter'] ?? '')."\n```\n\nCheck yourself: ".($exercise['answerGuide'] ?? ''));
    }

    /**
     * @param  array<int, string>  $paths
     * @return array<string, mixed>
     */
    private function mergePayloads(array $paths): array
    {
        $merged = ['profile' => [], 'certifications' => [], 'projects' => [], 'resources' => []];

        foreach ($paths as $path) {
            if (! File::exists($path)) {
                continue;
            }

            $payload = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);

            $merged['profile'] = array_replace($merged['profile'], $payload['profile'] ?? []);
            $merged['certifications'] = array_merge($merged['certifications'], $payload['certifications'] ?? []);
            $merged['projects'] = array_merge($merged['projects'], $payload['projects'] ?? []);
            $merged['resources'] = array_merge($merged['resources'], $payload['resources'] ?? []);
        }

        return $merged;
    }
}
