<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_dashboard_renders_seeded_certpath_content(): void
    {
        $this->seed();
        $this->actingAs(User::query()->where('email', 'learner@certpath.test')->firstOrFail());

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('CertPath 123');
        $response->assertSee('PL-300');
        $response->assertSee('10X Executive Performance Dashboard');
    }

    public function test_a_certification_workspace_renders_lessons_and_projects(): void
    {
        $this->seed();
        $this->actingAs(User::query()->where('email', 'learner@certpath.test')->firstOrFail());

        $response = $this->get('/certifications/pl-300');

        $response->assertStatus(200);
        $response->assertSee('Star schema fundamentals');
        $response->assertSee('Quick quiz');
        $response->assertSee('Procurement and Spend Analytics');
    }
}
