<?php

namespace Tests\Feature;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Certifications\Models\CertificationProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CurriculumWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_domain_for_their_certification(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();

        $this->actingAs($user)
            ->post(route('domains.store', ['certificationSlug' => $certification->slug]), [
                'name' => 'Deploy and maintain assets',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('certification_domains', [
            'certification_id' => $certification->id,
            'name' => 'Deploy and maintain assets',
        ]);
    }

    public function test_domain_weights_cannot_exceed_one_hundred_percent(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();

        $this->actingAs($user)
            ->from(route('certifications.show', ['certificationSlug' => $certification->slug]))
            ->post(route('domains.store', ['certificationSlug' => $certification->slug]), [
                'name' => 'Too much weight',
                'weight_percent' => 95,
            ])
            ->assertSessionHasErrors('domain');
    }

    public function test_user_can_create_an_ordered_topic_inside_a_domain(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();
        $domain = $certification->domains()->firstOrFail();

        $this->actingAs($user)
            ->post(route('topics.store', ['certificationSlug' => $certification->slug]), [
                'domain_id' => $domain->id,
                'name' => 'Data model security',
                'prerequisites' => 'Relationships and star schema basics.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('topics', [
            'certification_id' => $certification->id,
            'domain_id' => $domain->id,
            'name' => 'Data model security',
            'prerequisites' => 'Relationships and star schema basics.',
            'position' => $domain->topics()->count(),
        ]);
    }

    public function test_user_cannot_create_a_topic_inside_another_users_certification(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $otherUser = User::query()->create([
            'name' => 'Other learner',
            'email' => 'other-curriculum@certpath.test',
            'password' => Hash::make('password'),
        ]);
        $provider = CertificationProvider::query()->firstOrCreate(
            ['slug' => 'other-curriculum-provider'],
            ['name' => 'Other Curriculum Provider']
        );
        $otherCertification = Certification::query()->create([
            'user_id' => $otherUser->id,
            'provider_id' => $provider->id,
            'name' => 'Other Curriculum Certification',
            'slug' => 'other-curriculum-certification',
            'exam_code' => 'OTHER-3',
            'track_type' => 'paid_professional',
            'status' => 'Planned',
            'priority' => 1,
        ]);
        $otherDomain = $otherCertification->domains()->create(['name' => 'Private domain']);

        $this->actingAs($owner)
            ->post(route('topics.store', ['certificationSlug' => $otherCertification->slug]), [
                'domain_id' => $otherDomain->id,
                'name' => 'Private topic',
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('topics', ['name' => 'Private topic']);
    }
}
