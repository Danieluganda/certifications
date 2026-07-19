<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BudgetProjectCredentialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_track_exam_savings(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();

        $this->actingAs($user)->post(route('savings.store', ['certificationSlug' => $certification->slug]), [
            'amount' => 20,
            'currency' => 'USD',
            'transaction_type' => 'saving',
            'transaction_date' => '2026-07-19',
            'notes' => 'Weekly exam fund deposit.',
        ])->assertRedirect(route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'budget']));

        $this->assertDatabaseHas('savings_transactions', [
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'amount_minor' => 2000,
            'currency' => 'USD',
        ]);
        $this->assertSame(6500, $certification->refresh()->exam_saved_amount_minor);
    }

    public function test_user_can_create_project_and_upload_evidence(): void
    {
        Storage::fake('local');
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();

        $this->actingAs($user)->post(route('projects.store', ['certificationSlug' => $certification->slug]), [
            'title' => 'Executive dashboard evidence',
            'business_problem' => 'Leadership needs a trusted performance dashboard.',
            'scope_markdown' => 'Build model, report, and publish evidence.',
            'repository_url' => 'https://github.com/Danieluganda/certifications',
        ])->assertRedirect(route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'projects']));

        $project = $certification->projects()->where('title', 'Executive dashboard evidence')->firstOrFail();

        $this->actingAs($user)->post(route('projects.evidence.store', ['project' => $project->id]), [
            'evidence' => UploadedFile::fake()->create('dashboard.pdf', 120, 'application/pdf'),
            'description' => 'Dashboard export.',
            'review_notes' => 'Looks ready for portfolio.',
            'mark_completed' => '1',
        ])->assertRedirect();

        $project->refresh();

        $this->assertSame('Completed', $project->status);
        $this->assertNotNull($project->completed_at);
        $this->assertDatabaseHas('evidence_files', [
            'user_id' => $user->id,
            'evidenceable_type' => $project::class,
            'evidenceable_id' => $project->id,
            'original_name' => 'dashboard.pdf',
        ]);
    }

    public function test_user_can_record_an_earned_credential(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('slug', 'pl-300')->firstOrFail();

        $this->actingAs($user)->post(route('credentials.store', ['certificationSlug' => $certification->slug]), [
            'credential_name' => 'Power BI Data Analyst',
            'provider_name' => 'Microsoft',
            'issue_date' => '2026-08-01',
            'credential_id' => 'MS-123',
            'verification_url' => 'https://example.com/verify',
            'linkedin_added' => '1',
            'cv_added' => '1',
        ])->assertRedirect(route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'credentials']));

        $this->assertDatabaseHas('credentials', [
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'credential_name' => 'Power BI Data Analyst',
            'linkedin_added' => true,
            'cv_added' => true,
        ]);
    }
}
