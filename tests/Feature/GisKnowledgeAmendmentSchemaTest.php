<?php

namespace Tests\Feature;

use App\Domains\Specialisations\Models\AnalyticsProperty;
use App\Domains\Specialisations\Models\Dataset;
use App\Domains\Specialisations\Models\OntologyResource;
use App\Domains\Specialisations\Models\SearchIndex;
use App\Domains\Specialisations\Models\Specialisation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GisKnowledgeAmendmentSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_gis_knowledge_search_and_analytics_tables_exist(): void
    {
        foreach ([
            'specialisations',
            'certification_specialisation',
            'datasets',
            'ontology_resources',
            'search_indexes',
            'analytics_properties',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "{$table} table is missing.");
        }
    }

    public function test_amendment_records_connect_certifications_projects_and_specialisations(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('exam_code', 'PL-300')->firstOrFail();
        $project = $certification->projects()->firstOrFail();

        $specialisation = Specialisation::query()
            ->where('user_id', $user->id)
            ->where('slug', 'agricultural-knowledge-systems')
            ->firstOrFail();

        $certification->specialisations()->attach($specialisation);

        $dataset = Dataset::query()->create([
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'specialisation_id' => $specialisation->id,
            'name' => 'Uganda agricultural services sample',
            'dataset_type' => 'agricultural',
            'source_url' => 'https://example.test/agriculture.csv',
            'licence' => 'Open data sample',
            'description' => 'Training dataset for service coverage and resilience analysis.',
            'schema_metadata_json' => ['columns' => ['district', 'crop', 'service_count']],
            'last_verified_at' => now(),
        ]);

        $ontology = OntologyResource::query()->create([
            'user_id' => $user->id,
            'specialisation_id' => $specialisation->id,
            'name' => 'AGROVOC subset',
            'resource_type' => 'skos_vocabulary',
            'namespace_uri' => 'https://aims.fao.org/aos/agrovoc/',
            'source_url' => 'https://agrovoc.fao.org/',
            'version' => '2026 training subset',
            'licence' => 'FAO AGROVOC terms',
            'metadata_json' => ['languages' => ['en'], 'concept_count' => 50],
        ]);

        $searchIndex = SearchIndex::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'engine' => 'elasticsearch',
            'index_name' => 'agricultural_discovery_test',
            'status' => 'active',
            'document_count' => 1250,
            'configuration_json' => ['facets' => ['country', 'crop', 'year']],
            'last_indexed_at' => now(),
        ]);

        $analyticsProperty = AnalyticsProperty::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'provider' => 'GA4',
            'property_name' => 'CertPath Product Analytics',
            'property_identifier_encrypted' => encrypt('G-TRAINING123'),
            'status' => 'planned',
            'configuration_json' => ['events' => ['study_session_completed', 'quiz_submitted']],
        ]);

        $this->assertTrue($user->specialisations->contains($specialisation));
        $this->assertTrue($certification->specialisations->contains($specialisation));
        $this->assertTrue($specialisation->certifications->contains($certification));
        $this->assertTrue($specialisation->datasets->contains($dataset));
        $this->assertTrue($specialisation->ontologyResources->contains($ontology));
        $this->assertTrue($certification->datasets->contains($dataset));
        $this->assertTrue($project->searchIndexes->contains($searchIndex));
        $this->assertTrue($project->analyticsProperties->contains($analyticsProperty));
        $this->assertSame(['country', 'crop', 'year'], $searchIndex->configuration_json['facets']);
    }

    public function test_seeded_specialisations_are_visible_on_the_dashboard(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();

        $this->assertDatabaseHas('specialisations', [
            'user_id' => $user->id,
            'slug' => 'gis-and-remote-sensing',
            'name' => 'GIS and Remote Sensing',
        ]);
        $this->assertDatabaseHas('specialisations', [
            'user_id' => $user->id,
            'slug' => 'agricultural-knowledge-systems',
            'name' => 'Agricultural Knowledge Systems',
        ]);
        $this->assertDatabaseHas('datasets', [
            'user_id' => $user->id,
            'name' => 'Agricultural services and resilience sample',
            'dataset_type' => 'agricultural',
        ]);
        $this->assertDatabaseHas('ontology_resources', [
            'user_id' => $user->id,
            'name' => 'AGROVOC controlled vocabulary',
            'resource_type' => 'skos_vocabulary',
        ]);
        $this->assertDatabaseHas('search_indexes', [
            'user_id' => $user->id,
            'engine' => 'elasticsearch',
            'index_name' => 'agricultural_discovery',
        ]);
        $this->assertDatabaseHas('analytics_properties', [
            'user_id' => $user->id,
            'provider' => 'GA4',
            'property_name' => 'CertPath Product Analytics',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.page', ['dashboardPage' => 'specialisations']))
            ->assertOk()
            ->assertSee('GIS and Remote Sensing')
            ->assertSee('Agricultural Knowledge Systems')
            ->assertSee('Datasets')
            ->assertSee('AGROVOC controlled vocabulary')
            ->assertSee('Search lab')
            ->assertSee('Analytics properties');
    }
}
