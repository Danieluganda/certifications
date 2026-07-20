<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class CustomErrorPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.debug' => false]);
    }

    public function test_not_found_errors_use_the_certpath_error_page(): void
    {
        $this->get('/missing-study-page')
            ->assertNotFound()
            ->assertSee('CertPath 123')
            ->assertSee('We could not find that study page')
            ->assertDontSee('Symfony\\Component')
            ->assertDontSee('Exception trace');
    }

    public function test_method_errors_do_not_expose_framework_details(): void
    {
        $this->get('/logout')
            ->assertStatus(405)
            ->assertSee('That link cannot be opened this way')
            ->assertDontSee('MethodNotAllowedHttpException')
            ->assertDontSee('vendor\\laravel');
    }

    public function test_server_errors_use_a_private_message_when_debug_is_off(): void
    {
        Route::get('/__test-private-error-page', function (): void {
            throw new RuntimeException('Sensitive database driver detail');
        });

        $this->get('/__test-private-error-page')
            ->assertStatus(500)
            ->assertSee('The app hit an unexpected problem')
            ->assertDontSee('Sensitive database driver detail')
            ->assertDontSee('RuntimeException')
            ->assertDontSee('vendor\\laravel');
    }
}
