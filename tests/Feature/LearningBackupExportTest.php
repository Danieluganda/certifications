<?php

namespace Tests\Feature;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Certifications\Models\CertificationProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LearningBackupExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_download_personal_learning_backup(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();
        $certification->credentials()->create([
            'user_id' => $user->id,
            'credential_name' => 'PL-300 Power BI Data Analyst',
            'provider_name' => 'Microsoft',
            'issue_date' => '2026-07-19',
            'credential_id' => 'MS-123',
        ]);

        $response = $this->actingAs($user)->get(route('exports.learning-backup'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/json');
        $response->assertDownload('certpath-backup-'.now()->format('Y-m-d').'.json');

        $payload = json_decode($response->streamedContent(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('CertPath 123', $payload['product']);
        $this->assertSame('learner@certpath.test', $payload['user']['email']);
        $pl300 = collect($payload['certifications'])->firstWhere('exam_code', 'PL-300');

        $this->assertNotNull($pl300);
        $this->assertSame('PL-300 Power BI Data Analyst', $pl300['credentials'][0]['credential_name']);
    }

    public function test_learning_backup_does_not_include_another_users_certifications(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $otherUser = User::query()->create([
            'name' => 'Other learner',
            'email' => 'other-export@certpath.test',
            'password' => Hash::make('password'),
        ]);
        $provider = CertificationProvider::query()->firstOrCreate(
            ['slug' => 'other-export-provider'],
            ['name' => 'Other Export Provider']
        );

        Certification::query()->create([
            'user_id' => $otherUser->id,
            'provider_id' => $provider->id,
            'name' => 'Private External Certification',
            'slug' => 'private-external-certification',
            'exam_code' => 'PRIVATE-999',
            'track_type' => 'paid_professional',
            'status' => 'Planned',
            'priority' => 1,
        ]);

        $response = $this->actingAs($owner)->get(route('exports.learning-backup'));
        $payload = json_decode($response->streamedContent(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertNotContains('PRIVATE-999', array_column($payload['certifications'], 'exam_code'));
    }
}
