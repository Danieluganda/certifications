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
            'ESRI-GIS-MOOC',
            'NASA-ARSET',
            'QGIS-TRAINING',
            'AGROVOC',
            'SEMANTIC-WEB',
            'PCAD',
            'ELASTIC-ENGINEER',
            'GA4',
        ] as $examCode) {
            $this->assertDatabaseHas('certifications', [
                'user_id' => $user->id,
                'exam_code' => $examCode,
            ]);
        }

        $this->assertDatabaseHas('certifications', [
            'user_id' => $user->id,
            'exam_code' => 'SEMANTIC-WEB',
            'track_type' => 'skill_specialisation',
        ]);

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
