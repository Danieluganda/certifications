<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyMaterialsImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_materials_rule_seeds_priority_study_lessons(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();

        foreach ([
            'PL-300' => 'Executive dashboard evidence pack',
            'MS-APPLIED-POWER-AUTOMATE' => 'Power Automate partner workflow',
            'MS-APPLIED-COPILOT-STUDIO' => 'Copilot Studio study coach',
            'AI-FUNDAMENTALS' => 'AI use-case assessment',
            'AWS-EDUCATE-COMPUTE' => 'AWS compute portfolio lab',
            'AWS-EDUCATE-CLOUD-OPERATIONS' => 'Cloud operations runbook',
            'LFD121' => 'Secure CertPath review',
        ] as $examCode => $lessonTitle) {
            $certification = $user->certifications()
                ->where('exam_code', $examCode)
                ->firstOrFail();

            $this->assertDatabaseHas('lessons', [
                'certification_id' => $certification->id,
                'title' => $lessonTitle,
            ]);
        }
    }

    public function test_materials_rule_seeds_projects_and_official_resources(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();

        foreach ([
            'Power Automate Partner Workflow',
            'Copilot Studio Study Coach',
            'AWS Educate Combined Cloud Portfolio',
            'Secure CertPath or ProcureFlow',
            'Uganda MSME Digital Public Infrastructure Concept',
        ] as $projectTitle) {
            $this->assertDatabaseHas('projects', [
                'user_id' => $user->id,
                'title' => $projectTitle,
            ]);
        }

        foreach ([
            'Microsoft Applied Skills catalogue',
            'AWS Educate',
            'Linux Foundation LFD121 course page',
            'Cisco Skills for All Ethical Hacker',
            'ADBI E-Learning',
        ] as $resourceTitle) {
            $this->assertDatabaseHas('resources', [
                'user_id' => $user->id,
                'title' => $resourceTitle,
                'trust_level' => 'official',
            ]);
        }
    }
}
