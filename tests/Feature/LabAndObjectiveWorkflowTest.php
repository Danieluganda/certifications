<?php

namespace Tests\Feature;

use App\Domains\Curriculum\Models\Lab;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LabAndObjectiveWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_objective_versions_and_labs_are_visible_in_workspace(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();

        $this->assertDatabaseHas('certification_objective_versions', [
            'certification_id' => $certification->id,
            'version_label' => 'Current Microsoft PL-300 study guide',
            'is_current' => true,
        ]);
        $this->assertDatabaseHas('labs', [
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'title' => 'Power BI model evidence lab',
            'status' => 'Planned',
        ]);

        $this->actingAs($user)
            ->get(route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'curriculum']))
            ->assertOk()
            ->assertSee('Objective versions')
            ->assertSee('Current Microsoft PL-300 study guide')
            ->assertSee('Add objective version');

        $this->actingAs($user)
            ->get(route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'labs']))
            ->assertOk()
            ->assertSee('Labs')
            ->assertSee('Power BI model evidence lab')
            ->assertSee('Add lab');
    }

    public function test_user_can_create_current_objective_version(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();

        $this->actingAs($user)
            ->post(route('objective-versions.store', ['certificationSlug' => $certification->slug]), [
                'version_label' => 'PL-300 refreshed objectives',
                'source_url' => 'https://learn.microsoft.com/en-us/credentials/certifications/resources/study-guides/pl-300',
                'effective_from' => '2026-08-01',
                'is_current' => '1',
                'notes' => 'Track the refreshed objective map.',
            ])
            ->assertRedirect(route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'curriculum']));

        $this->assertDatabaseHas('certification_objective_versions', [
            'certification_id' => $certification->id,
            'version_label' => 'PL-300 refreshed objectives',
            'is_current' => true,
        ]);
        $this->assertSame(1, $certification->objectiveVersions()->where('is_current', true)->count());
    }

    public function test_user_can_create_and_complete_a_lab(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();
        $topic = $certification->topics()->firstOrFail();

        $this->actingAs($user)
            ->post(route('labs.store', ['certificationSlug' => $certification->slug]), [
                'topic_id' => $topic->id,
                'title' => 'DAX evidence lab',
                'objective' => 'Prove DAX measure behaviour in filter context.',
                'instructions_markdown' => "Create two measures.\nTest them against slicers.\nCapture notes.",
                'expected_outcome' => 'A short DAX evidence note.',
                'estimated_minutes' => 45,
                'is_required' => '1',
            ])
            ->assertRedirect(route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'labs']));

        $lab = Lab::query()->where('title', 'DAX evidence lab')->firstOrFail();

        $this->assertSame($user->id, $lab->user_id);
        $this->assertSame($certification->id, $lab->certification_id);
        $this->assertSame($topic->id, $lab->topic_id);

        $this->actingAs($user)
            ->post(route('labs.complete', ['lab' => $lab->id]), [
                'reflection' => 'Slicers changed the measure through filter context.',
            ])
            ->assertRedirect(route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'labs']));

        $lab->refresh();

        $this->assertSame('completed', $lab->status);
        $this->assertSame('Slicers changed the measure through filter context.', $lab->reflection);
        $this->assertNotNull($lab->completed_at);
    }

    public function test_user_cannot_complete_another_users_lab(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $otherUser = User::query()->create([
            'name' => 'Other lab learner',
            'email' => 'other-lab@certpath.test',
            'password' => Hash::make('password'),
        ]);
        $certification = $owner->certifications()->where('slug', 'pl-300')->firstOrFail();

        $lab = Lab::query()->create([
            'user_id' => $otherUser->id,
            'certification_id' => $certification->id,
            'title' => 'Private lab',
            'objective' => 'Private objective',
            'instructions_markdown' => 'Private instructions',
        ]);

        $this->actingAs($owner)
            ->post(route('labs.complete', ['lab' => $lab->id]))
            ->assertNotFound();

        $this->assertSame('Planned', $lab->refresh()->status);
    }
}
