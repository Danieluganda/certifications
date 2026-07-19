<?php

namespace Database\Seeders;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Certifications\Models\CertificationProvider;
use App\Domains\Curriculum\Models\CertificationDomain;
use App\Domains\Curriculum\Models\Lesson;
use App\Domains\Curriculum\Models\Topic;
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
        $path = database_path('data/certifications/certpath-seed.json');
        $payload = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);

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

                Lesson::query()->updateOrCreate(
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
    }

    private function formatExample(array $example): string
    {
        return trim(($example['intro'] ?? '')."\n\n```text\n".($example['code'] ?? '')."\n```\n\n".($example['explanation'] ?? ''));
    }

    private function formatExercise(array $exercise): string
    {
        return trim(($exercise['prompt'] ?? '')."\n\n```text\n".($exercise['starter'] ?? '')."\n```\n\nCheck yourself: ".($exercise['answerGuide'] ?? ''));
    }
}
