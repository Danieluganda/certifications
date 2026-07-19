<?php

namespace Tests\Feature;

use App\Domains\Certifications\Actions\ActivateFreeCredential;
use App\Domains\Certifications\Actions\SetPrimaryCertification;
use App\Domains\Certifications\Models\Certification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\TestCase;

class CertificationActivationRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_set_primary_certification_allows_only_one_paid_primary(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $dp600 = Certification::query()->where('user_id', $user->id)->where('exam_code', 'DP-600')->firstOrFail();

        app(SetPrimaryCertification::class)->execute($user, $dp600);

        $this->assertTrue($dp600->fresh()->is_primary);
        $this->assertFalse(Certification::query()->where('user_id', $user->id)->where('exam_code', 'PL-300')->firstOrFail()->is_primary);
        $this->assertSame(1, Certification::query()->where('user_id', $user->id)->where('track_type', 'paid_professional')->where('is_primary', true)->count());
    }

    public function test_free_credential_cannot_be_primary(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $credential = Certification::query()->where('user_id', $user->id)->where('exam_code', 'AI-FUNDAMENTALS')->firstOrFail();

        $this->expectException(InvalidArgumentException::class);

        app(SetPrimaryCertification::class)->execute($user, $credential);
    }

    public function test_free_credential_activation_respects_profile_limit(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $user->profile()->update(['max_active_free_credentials' => 1]);
        $credential = Certification::query()->where('user_id', $user->id)->where('exam_code', 'AI-FUNDAMENTALS')->firstOrFail();

        app(ActivateFreeCredential::class)->execute($user, $credential);
        $this->assertSame('Active', $credential->fresh()->status);

        $secondCredential = $credential->replicate(['slug', 'exam_code', 'is_primary']);
        $secondCredential->forceFill([
            'slug' => 'second-free-credential',
            'exam_code' => 'SECOND-FREE',
            'status' => 'Planned',
            'is_primary' => false,
        ])->save();

        $this->expectException(InvalidArgumentException::class);

        app(ActivateFreeCredential::class)->execute($user, $secondCredential);
    }

    public function test_database_prevents_two_primary_paid_certifications_for_one_user(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();

        $this->expectException(\Throwable::class);

        Certification::query()
            ->where('user_id', $user->id)
            ->where('exam_code', 'DP-600')
            ->update(['is_primary' => true]);
    }

    public function test_projects_and_resources_have_direct_user_ownership(): void
    {
        $this->seed();

        $this->assertGreaterThan(0, DB::table('projects')->whereNotNull('user_id')->count());
        $this->assertSame(0, DB::table('projects')->whereNull('user_id')->count());
        $this->assertGreaterThan(0, DB::table('resources')->whereNotNull('user_id')->count());
        $this->assertSame(0, DB::table('resources')->whereNull('user_id')->count());
    }
}
