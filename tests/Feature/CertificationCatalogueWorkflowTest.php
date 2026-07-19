<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificationCatalogueWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_a_paid_certification(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();

        $this->actingAs($user)
            ->post(route('certifications.store'), [
                'provider_name' => 'CompTIA',
                'name' => 'Security+',
                'exam_code' => 'SY0-701',
                'track_type' => 'paid_professional',
                'weekly_minutes' => 180,
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('certifications', [
            'user_id' => $user->id,
            'name' => 'Security+',
            'slug' => 'sy0-701',
            'track_type' => 'paid_professional',
            'status' => 'Planned',
            'weekly_minutes' => 180,
        ]);
    }

    public function test_user_can_add_a_free_credential(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();

        $this->actingAs($user)
            ->post(route('certifications.store'), [
                'provider_name' => 'Cisco',
                'name' => 'Networking Basics',
                'track_type' => 'free_credential',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('certifications', [
            'user_id' => $user->id,
            'name' => 'Networking Basics',
            'slug' => 'networking-basics',
            'track_type' => 'free_credential',
        ]);
    }

    public function test_user_can_mark_a_paid_certification_primary_from_the_dashboard_workflow(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'az-104')->firstOrFail();

        $this->actingAs($user)
            ->post(route('certifications.primary.store', ['certificationSlug' => $certification->slug]))
            ->assertRedirect();

        $this->assertTrue($certification->refresh()->is_primary);
        $this->assertSame('Active', $certification->status);
        $this->assertFalse($user->certifications()->where('slug', 'pl-300')->firstOrFail()->is_primary);
    }

    public function test_user_can_activate_a_free_credential_from_the_dashboard_workflow(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $user->profile()->update(['max_active_free_credentials' => 3]);
        $credential = $user->certifications()->create([
            'provider_id' => $user->certifications()->firstOrFail()->provider_id,
            'name' => 'GitHub Foundations',
            'slug' => 'github-foundations',
            'exam_code' => 'GH-FOUND',
            'track_type' => 'free_credential',
            'status' => 'Planned',
            'priority' => 3,
        ]);

        $this->actingAs($user)
            ->post(route('certifications.free-activation.store', ['certificationSlug' => $credential->slug]))
            ->assertRedirect();

        $this->assertSame('Active', $credential->refresh()->status);
    }
}
