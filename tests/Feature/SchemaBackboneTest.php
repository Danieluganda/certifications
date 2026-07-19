<?php

namespace Tests\Feature;

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Budgeting\Models\ExamBudget;
use App\Domains\Budgeting\Models\Voucher;
use App\Domains\Certifications\Models\CertificationObjectiveVersion;
use App\Domains\Curriculum\Models\Lab;
use App\Domains\Notes\Models\Tag;
use App\Domains\Notifications\Models\AppNotification;
use App\Domains\Planning\Models\StudyPlan;
use App\Domains\Planning\Models\StudySession;
use App\Domains\Planning\Models\StudySessionEvent;
use App\Domains\Planning\Models\WeeklyAvailability;
use App\Domains\Practice\Models\QuizAttempt;
use App\Domains\Practice\Models\QuizBlueprint;
use App\Domains\Progress\Models\ProgressSnapshot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaBackboneTest extends TestCase
{
    use RefreshDatabase;

    public function test_schema_backbone_tables_exist(): void
    {
        foreach ([
            'weekly_availabilities',
            'certification_objective_versions',
            'topic_prerequisites',
            'tags',
            'taggables',
            'study_plans',
            'study_session_events',
            'quiz_blueprints',
            'labs',
            'exam_budgets',
            'vouchers',
            'progress_snapshots',
            'notifications',
            'audit_logs',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "{$table} table is missing.");
        }

        foreach (['study_plan_id', 'priority_score', 'confidence'] as $column) {
            $this->assertTrue(Schema::hasColumn('study_sessions', $column), "study_sessions.{$column} is missing.");
        }

        $this->assertTrue(Schema::hasColumn('quiz_attempts', 'blueprint_id'));
        $this->assertTrue(Schema::hasColumn('savings_transactions', 'exam_budget_id'));
        $this->assertTrue(Schema::hasColumn('certification_domains', 'objective_version_id'));
    }

    public function test_schema_backbone_records_can_be_connected(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('exam_code', 'PL-300')->firstOrFail();
        $domain = $certification->domains()->firstOrFail();
        $topic = $certification->topics()->firstOrFail();
        $lesson = $certification->lessons()->where('topic_id', $topic->id)->firstOrFail();

        $availability = WeeklyAvailability::query()->create([
            'user_id' => $user->id,
            'day_of_week' => 1,
            'start_time' => '19:00',
            'end_time' => '20:30',
        ]);

        $objectiveVersion = CertificationObjectiveVersion::query()->create([
            'certification_id' => $certification->id,
            'version_label' => 'PL-300 2026 objective map',
            'source_url' => 'https://learn.microsoft.com/credentials/certifications/power-bi-data-analyst-associate/',
            'effective_from' => '2026-01-01',
            'is_current' => true,
        ]);

        $domain->update(['objective_version_id' => $objectiveVersion->id, 'description' => 'Model and transform data objectives.']);

        $plan = StudyPlan::query()->create([
            'user_id' => $user->id,
            'name' => 'PL-300 weekday plan',
            'starts_on' => '2026-07-20',
            'ends_on' => '2026-07-26',
            'status' => 'active',
            'generated_by' => 'system',
            'generation_context' => ['availability_ids' => [$availability->id]],
        ]);

        $session = StudySession::query()->create([
            'user_id' => $user->id,
            'study_plan_id' => $plan->id,
            'certification_id' => $certification->id,
            'lesson_id' => $lesson->id,
            'topic_id' => $topic->id,
            'activity_type' => 'Lesson',
            'scheduled_for' => '2026-07-20 19:00:00',
            'scheduled_start' => '2026-07-20 19:00:00',
            'scheduled_end' => '2026-07-20 20:00:00',
            'planned_minutes' => 60,
            'priority_score' => 95,
            'confidence' => 4,
        ]);

        $event = StudySessionEvent::query()->create([
            'study_session_id' => $session->id,
            'event_type' => 'created',
            'occurred_at' => now(),
            'metadata' => ['source' => 'schema_backbone_test'],
        ]);

        $blueprint = QuizBlueprint::query()->create([
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'name' => 'PL-300 domain mock',
            'mode' => 'mock',
            'question_count' => 20,
            'duration_minutes' => 40,
            'passing_score' => 70,
            'configuration' => ['domains' => [$domain->id]],
        ]);

        $attempt = QuizAttempt::query()->create([
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'blueprint_id' => $blueprint->id,
            'attempt_type' => 'mock',
            'status' => 'In_progress',
            'started_at' => now(),
            'total_questions' => 20,
        ]);

        $lab = Lab::query()->create([
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'topic_id' => $topic->id,
            'title' => 'Build a star schema lab',
            'objective' => 'Model facts and dimensions for a Power BI dataset.',
            'instructions_markdown' => 'Create fact and dimension tables, then document the relationships.',
            'expected_outcome' => 'A clean star schema with evidence notes.',
        ]);

        $budget = ExamBudget::query()->create([
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'target_amount_minor' => 16500,
            'currency' => 'USD',
            'target_date' => '2026-09-01',
        ]);

        $voucher = Voucher::query()->create([
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'discount_type' => 'percentage',
            'discount_value' => 25,
            'currency' => 'USD',
            'expires_at' => '2026-08-31 23:59:00',
            'source' => 'student offer',
        ]);

        $snapshot = ProgressSnapshot::query()->create([
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'period_type' => 'weekly',
            'period_start' => '2026-07-20',
            'period_end' => '2026-07-26',
            'planned_minutes' => 300,
            'completed_minutes' => 60,
            'sessions_completed' => 1,
            'quizzes_completed' => 1,
            'average_score' => 78,
            'reviews_completed' => 12,
            'snapshot_data' => ['readiness' => 42],
        ]);

        $notification = AppNotification::query()->create([
            'user_id' => $user->id,
            'type' => 'study_session_due',
            'title' => 'Study session due',
            'message' => 'Your PL-300 study session starts at 7 PM.',
            'action_url' => '/dashboard/planner',
        ]);

        $audit = AuditLog::query()->create([
            'user_id' => $user->id,
            'action' => 'study_session.created',
            'auditable_type' => StudySession::class,
            'auditable_id' => $session->id,
            'after_data' => ['planned_minutes' => 60],
        ]);

        $tag = Tag::query()->create(['user_id' => $user->id, 'name' => 'DAX', 'slug' => 'dax']);
        DB::table('taggables')->insert([
            'tag_id' => $tag->id,
            'taggable_type' => Lab::class,
            'taggable_id' => $lab->id,
        ]);

        $this->assertTrue($plan->sessions->contains($session));
        $this->assertTrue($session->events->contains($event));
        $this->assertTrue($blueprint->attempts->contains($attempt));
        $this->assertTrue($certification->labs->contains($lab));
        $this->assertTrue($certification->examBudgets->contains($budget));
        $this->assertTrue($certification->vouchers->contains($voucher));
        $this->assertTrue($user->progressSnapshots->contains($snapshot));
        $this->assertTrue($user->appNotifications->contains($notification));
        $this->assertTrue($user->auditLogs->contains($audit));
        $this->assertDatabaseHas('taggables', ['tag_id' => $tag->id, 'taggable_id' => $lab->id]);
    }
}
