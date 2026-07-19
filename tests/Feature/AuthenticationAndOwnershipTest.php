<?php

namespace Tests\Feature;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Certifications\Models\CertificationProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAndOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_seeded_user_can_login(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'email' => 'learner@certpath.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_user_cannot_view_another_users_certification_workspace(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $otherUser = User::query()->create([
            'name' => 'Other learner',
            'email' => 'other@certpath.test',
            'password' => Hash::make('password'),
        ]);
        $provider = CertificationProvider::query()->firstOrCreate(
            ['slug' => 'other-provider'],
            ['name' => 'Other Provider']
        );

        Certification::query()->create([
            'user_id' => $otherUser->id,
            'provider_id' => $provider->id,
            'name' => 'Other Certification',
            'slug' => 'other-certification',
            'exam_code' => 'OTHER-1',
            'track_type' => 'paid_professional',
            'status' => 'Planned',
            'priority' => 1,
            'is_primary' => false,
        ]);

        $this->actingAs($owner);

        $this->get('/certifications/other-certification')->assertNotFound();
    }
}
