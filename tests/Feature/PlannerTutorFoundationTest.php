<?php

namespace Tests\Feature;

use App\Domains\Planning\Models\PlannerRecommendation;
use App\Domains\Planning\Models\SessionTask;
use App\Domains\Planning\Models\StudyGoal;
use App\Domains\Planning\Models\StudySession;
use App\Domains\Planning\Models\StudyStreak;
use App\Domains\Projects\Models\ProjectMilestone;
use App\Domains\Tutor\Models\LearnerMisconception;
use App\Domains\Tutor\Models\TutorFeedback;
use App\Domains\Tutor\Models\TutorMessage;
use App\Domains\Tutor\Models\TutorRecommendation;
use App\Domains\Tutor\Models\TutorSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlannerTutorFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_planner_and_tutor_tables_exist(): void
    {
        foreach ([
            'study_goals',
            'project_milestones',
            'session_tasks',
            'study_streaks',
            'planner_recommendations',
            'tutor_sessions',
            'tutor_messages',
            'tutor_recommendations',
            'learner_misconceptions',
            'tutor_feedback',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "{$table} table is missing.");
        }

        foreach ([
            'topic_id',
            'scheduled_start',
            'scheduled_end',
            'actual_minutes',
            'target_description',
            'priority',
            'started_at',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('study_sessions', $column), "study_sessions.{$column} is missing.");
        }
    }

    public function test_planner_records_connect_to_study_sessions_and_project_milestones(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('exam_code', 'PL-300')->firstOrFail();
        $lesson = $certification->lessons()->firstOrFail();
        $topic = $lesson->topic()->firstOrFail();
        $project = $certification->projects()->firstOrFail();

        $goal = StudyGoal::query()->create([
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'goal_period' => 'weekly',
            'goal_type' => 'questions_answered',
            'target_value' => 100,
            'current_value' => 15,
            'unit' => 'questions',
            'starts_on' => '2026-07-20',
            'ends_on' => '2026-07-26',
        ]);

        $session = StudySession::query()->create([
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'lesson_id' => $lesson->id,
            'topic_id' => $topic->id,
            'activity_type' => 'Lesson',
            'scheduled_for' => '2026-07-20 19:30:00',
            'scheduled_start' => '2026-07-20 19:30:00',
            'scheduled_end' => '2026-07-20 20:30:00',
            'planned_minutes' => 60,
            'target_description' => 'Complete lesson and answer 10 questions.',
            'priority' => 1,
        ]);

        $milestone = ProjectMilestone::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Draft dashboard evidence checklist',
            'description' => 'List model, DAX, RLS, and deployment proof.',
            'position' => 1,
        ]);

        $task = SessionTask::query()->create([
            'study_session_id' => $session->id,
            'task_type' => 'project_task',
            'lesson_id' => $lesson->id,
            'topic_id' => $topic->id,
            'project_milestone_id' => $milestone->id,
            'title' => 'Update evidence checklist',
            'target_value' => 1,
            'position' => 1,
        ]);

        $streak = StudyStreak::query()->create([
            'user_id' => $user->id,
            'current_streak' => 3,
            'longest_streak' => 5,
            'last_qualified_date' => '2026-07-20',
            'freeze_count' => 1,
        ]);

        $recommendation = PlannerRecommendation::query()->create([
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'recommendation_type' => 'weak_topic_quiz',
            'reason' => 'DAX confidence is still low.',
            'recommended_date' => '2026-07-21',
            'duration_minutes' => 30,
            'priority' => 1,
        ]);

        $this->assertTrue($user->studyGoals->contains($goal));
        $this->assertTrue($user->studyStreak->is($streak));
        $this->assertTrue($user->plannerRecommendations->contains($recommendation));
        $this->assertTrue($certification->studyGoals->contains($goal));
        $this->assertTrue($session->tasks->contains($task));
        $this->assertTrue($project->milestones->contains($milestone));
        $this->assertTrue($topic->studySessions->contains($session));
    }

    public function test_tutor_records_keep_source_grounded_history_and_feedback(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'learner@certpath.test')->firstOrFail();
        $certification = $user->certifications()->where('exam_code', 'PL-300')->firstOrFail();
        $lesson = $certification->lessons()->firstOrFail();
        $topic = $lesson->topic()->firstOrFail();
        $project = $certification->projects()->firstOrFail();

        $session = TutorSession::query()->create([
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'topic_id' => $topic->id,
            'project_id' => $project->id,
            'mode' => 'explain',
            'title' => 'Explain DAX measures',
            'started_at' => now(),
        ]);

        $message = TutorMessage::query()->create([
            'tutor_session_id' => $session->id,
            'role' => 'tutor',
            'message' => 'A measure is evaluated in filter context and should be checked against report interactions.',
            'source_references' => [
                ['type' => 'lesson', 'title' => $lesson->title],
                ['type' => 'resource', 'title' => 'Official PL-300 study guide'],
            ],
            'model_metadata' => ['provider' => 'offline-foundation-test'],
        ]);

        $recommendation = TutorRecommendation::query()->create([
            'user_id' => $user->id,
            'tutor_session_id' => $session->id,
            'certification_id' => $certification->id,
            'topic_id' => $topic->id,
            'recommendation_type' => 'take_quiz',
            'title' => 'Practise DAX filter context',
            'reason' => 'The learner confused calculated columns and measures.',
            'priority' => 1,
        ]);

        $misconception = LearnerMisconception::query()->create([
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'topic_id' => $topic->id,
            'description' => 'Treats measures like precomputed row values.',
            'first_detected_at' => now(),
            'last_detected_at' => now(),
            'evidence' => ['source' => 'knowledge_check'],
        ]);

        $feedback = TutorFeedback::query()->create([
            'user_id' => $user->id,
            'tutor_message_id' => $message->id,
            'rating' => 5,
            'feedback' => 'Helpful and sourced.',
            'was_helpful' => true,
            'was_accurate' => true,
        ]);

        $this->assertTrue($user->tutorSessions->contains($session));
        $this->assertTrue($certification->tutorSessions->contains($session));
        $this->assertTrue($topic->tutorSessions->contains($session));
        $this->assertTrue($session->messages->contains($message));
        $this->assertTrue($session->recommendations->contains($recommendation));
        $this->assertTrue($user->learnerMisconceptions->contains($misconception));
        $this->assertTrue($message->feedback->contains($feedback));
        $this->assertSame('Official PL-300 study guide', $message->source_references[1]['title']);
    }
}
