<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GisKnowledgeSystemsAmendmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_amendment_seeds_gis_knowledge_search_and_analytics_pathways(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();

        foreach ([
            'PL-300',
            'DP-600',
            'AZ-104',
            'DP-700',
            'AZ-305',
            'PMP',
            'CISA',
            'CRISC',
            'TOGAF-FOUNDATION',
            'TOGAF-PRACTITIONER',
            'CISSP',
            'PCAD',
            'ARCGIS-PRO-FOUNDATION',
            'ARCGIS-DEVELOPER-FOUNDATION',
            'ARCGIS-PYTHON',
            'ARCGIS-ONLINE-ADMIN',
            'FME-PROFESSIONAL',
            'ELASTIC-ENGINEER',
            'GISP',
        ] as $examCode) {
            $this->assertDatabaseHas('certifications', [
                'user_id' => $user->id,
                'exam_code' => $examCode,
                'track_type' => 'paid_professional',
            ]);
        }

        foreach ([
            'MS-APPLIED-POWER-AUTOMATE',
            'MS-APPLIED-COPILOT-STUDIO',
            'MS-APPLIED-AUTONOMOUS-AGENTS',
            'AWS-EDUCATE-COMPUTE',
            'AWS-EDUCATE-NETWORKING',
            'AWS-EDUCATE-DATABASES',
            'AWS-EDUCATE-CLOUD-OPERATIONS',
            'AI-FUNDAMENTALS',
            'IBM-PM-FUNDAMENTALS',
            'IBM-DESIGN-THINKING',
            'LFD121',
            'CISCO-ETHICAL-HACKER',
            'PMI-KICKOFF',
            'HP-LIFE',
            'ADBI-DPI',
            'ESRI-GIS-MOOC',
            'NASA-ARSET',
            'EO-COLLEGE',
            'QGIS-TRAINING',
            'AGROVOC',
            'GA4',
        ] as $examCode) {
            $this->assertDatabaseHas('certifications', [
                'user_id' => $user->id,
                'exam_code' => $examCode,
                'track_type' => 'free_credential',
            ]);
        }

        foreach ([
            'SEMANTIC-WEB',
            'SEARCH-IR',
            'APACHE-SOLR',
            'AGRI-DATASETS',
            'BIBLIO-DATASETS',
            'GIS-REMOTE-SENSING',
            'POSTGIS-SPATIAL-DATA',
            'R-ANALYTICS',
        ] as $examCode) {
            $this->assertDatabaseHas('certifications', [
                'user_id' => $user->id,
                'exam_code' => $examCode,
                'track_type' => 'skill_specialisation',
            ]);
        }

        $this->assertDatabaseHas('projects', [
            'user_id' => $user->id,
            'title' => 'Uganda MSME Accessibility and Service Coverage Map',
        ]);

        $this->assertDatabaseHas('projects', [
            'user_id' => $user->id,
            'title' => 'AGROVOC-Powered Agricultural Knowledge Repository',
        ]);

        $this->assertDatabaseHas('resources', [
            'user_id' => $user->id,
            'title' => 'SPARQL Query Language',
        ]);
    }

    public function test_user_can_add_a_skill_specialisation(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();

        $this->actingAs($user)
            ->post(route('certifications.store'), [
                'provider_name' => 'Applied Portfolio',
                'name' => 'PostGIS Spatial SQL',
                'exam_code' => 'POSTGIS',
                'track_type' => 'skill_specialisation',
                'weekly_minutes' => 60,
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('certifications', [
            'user_id' => $user->id,
            'name' => 'PostGIS Spatial SQL',
            'slug' => 'postgis',
            'track_type' => 'skill_specialisation',
            'weekly_minutes' => 60,
        ]);
    }
}
