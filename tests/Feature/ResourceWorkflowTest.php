<?php

namespace Tests\Feature;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Certifications\Models\CertificationProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResourceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_a_resource_to_their_certification(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();
        $domain = $certification->domains()->firstOrFail();
        $topic = $domain->topics()->firstOrFail();

        $this->actingAs($user)
            ->post(route('resources.store', ['certificationSlug' => $certification->slug]), [
                'domain_id' => $domain->id,
                'topic_id' => $topic->id,
                'title' => 'Microsoft semantic model guidance',
                'resource_type' => 'Official documentation',
                'provider_name' => 'Microsoft Learn',
                'url' => 'https://learn.microsoft.com/power-bi/',
                'trust_level' => 'Official',
                'copyright_status' => 'linked_not_copied',
                'copyright_note' => 'Use as external reference only.',
                'status' => 'in progress',
                'rating' => 5,
            ])
            ->assertRedirect(route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'resources']));

        $this->assertDatabaseHas('resources', [
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'domain_id' => $domain->id,
            'topic_id' => $topic->id,
            'title' => 'Microsoft semantic model guidance',
            'trust_level' => 'Official',
            'status' => 'in progress',
            'rating' => 5,
        ]);
    }

    public function test_resource_requires_a_url_or_file_path(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();

        $this->actingAs($user)
            ->from(route('certifications.show', ['certificationSlug' => $certification->slug]))
            ->post(route('resources.store', ['certificationSlug' => $certification->slug]), [
                'title' => 'Missing location',
                'resource_type' => 'Article',
                'provider_name' => 'Personal',
                'trust_level' => 'personal',
                'copyright_status' => 'personal_notes_allowed',
                'status' => 'Not started',
            ])
            ->assertSessionHasErrors('resource');
    }

    public function test_user_cannot_attach_resource_to_another_users_certification(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $otherUser = User::query()->create([
            'name' => 'Other learner',
            'email' => 'other-resource@certpath.test',
            'password' => Hash::make('password'),
        ]);
        $provider = CertificationProvider::query()->firstOrCreate(
            ['slug' => 'other-resource-provider'],
            ['name' => 'Other Resource Provider']
        );
        $otherCertification = Certification::query()->create([
            'user_id' => $otherUser->id,
            'provider_id' => $provider->id,
            'name' => 'Other Resource Certification',
            'slug' => 'other-resource-certification',
            'exam_code' => 'OTHER-4',
            'track_type' => 'paid_professional',
            'status' => 'Planned',
            'priority' => 1,
        ]);

        $this->actingAs($owner)
            ->post(route('resources.store', ['certificationSlug' => $otherCertification->slug]), [
                'title' => 'Private resource',
                'resource_type' => 'Article',
                'provider_name' => 'Private',
                'url' => 'https://example.com/private',
                'trust_level' => 'personal',
                'copyright_status' => 'personal_notes_allowed',
                'status' => 'Not started',
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('resources', ['title' => 'Private resource']);
    }
}
