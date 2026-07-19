<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certification_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('goal_period');
            $table->string('goal_type');
            $table->unsignedInteger('target_value');
            $table->unsignedInteger('current_value')->default(0);
            $table->string('unit');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['user_id', 'goal_period', 'status']);
            $table->index(['user_id', 'starts_on', 'ends_on']);
        });

        Schema::table('study_sessions', function (Blueprint $table) {
            $table->foreignId('topic_id')->nullable()->after('lesson_id')->constrained('topics')->nullOnDelete();
            $table->dateTime('scheduled_start')->nullable()->after('activity_type');
            $table->dateTime('scheduled_end')->nullable()->after('scheduled_start');
            $table->unsignedInteger('actual_minutes')->nullable()->after('planned_minutes');
            $table->text('target_description')->nullable()->after('actual_minutes');
            $table->unsignedTinyInteger('priority')->default(3)->after('target_description');
            $table->dateTime('started_at')->nullable()->after('priority');

            $table->index(['user_id', 'scheduled_start']);
            $table->index(['topic_id', 'status']);
        });

        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('target_date')->nullable();
            $table->string('status')->default('Planned');
            $table->unsignedInteger('position')->default(0);
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['project_id', 'position']);
        });

        Schema::create('session_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_session_id')->constrained()->cascadeOnDelete();
            $table->string('task_type');
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained('topics')->nullOnDelete();
            $table->foreignId('quiz_attempt_id')->nullable()->constrained('quiz_attempts')->nullOnDelete();
            $table->foreignId('project_milestone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->unsignedInteger('target_value')->nullable();
            $table->unsignedInteger('actual_value')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->string('status')->default('Pending');
            $table->timestamps();

            $table->index(['study_session_id', 'position']);
            $table->index(['task_type', 'status']);
        });

        Schema::create('study_streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('longest_streak')->default(0);
            $table->date('last_qualified_date')->nullable();
            $table->unsignedInteger('freeze_count')->default(0);
            $table->timestamps();
        });

        Schema::create('planner_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certification_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('recommendation_type');
            $table->text('reason');
            $table->date('recommended_date')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->unsignedTinyInteger('priority')->default(3);
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('dismissed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'recommended_date']);
            $table->index(['user_id', 'accepted_at', 'dismissed_at']);
        });

        Schema::create('tutor_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certification_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained('topics')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('mode');
            $table->string('title');
            $table->string('status')->default('active');
            $table->dateTime('started_at');
            $table->dateTime('completed_at')->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['certification_id', 'mode']);
        });

        Schema::create('tutor_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_session_id')->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->longText('message');
            $table->json('source_references')->nullable();
            $table->json('model_metadata')->nullable();
            $table->timestamps();

            $table->index(['tutor_session_id', 'created_at']);
        });

        Schema::create('tutor_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tutor_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certification_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained('topics')->nullOnDelete();
            $table->string('recommendation_type');
            $table->string('title');
            $table->text('reason');
            $table->unsignedTinyInteger('priority')->default(3);
            $table->string('status')->default('proposed');
            $table->foreignId('scheduled_session_id')->nullable()->constrained('study_sessions')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['recommendation_type', 'priority']);
        });

        Schema::create('learner_misconceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certification_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained('topics')->nullOnDelete();
            $table->text('description');
            $table->dateTime('first_detected_at');
            $table->dateTime('last_detected_at');
            $table->unsignedInteger('detection_count')->default(1);
            $table->dateTime('resolved_at')->nullable();
            $table->json('evidence')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'resolved_at']);
            $table->index(['certification_id', 'topic_id']);
        });

        Schema::create('tutor_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tutor_message_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('feedback')->nullable();
            $table->boolean('was_helpful')->nullable();
            $table->boolean('was_accurate')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'was_helpful']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_feedback');
        Schema::dropIfExists('learner_misconceptions');
        Schema::dropIfExists('tutor_recommendations');
        Schema::dropIfExists('tutor_messages');
        Schema::dropIfExists('tutor_sessions');
        Schema::dropIfExists('planner_recommendations');
        Schema::dropIfExists('study_streaks');
        Schema::dropIfExists('session_tasks');
        Schema::dropIfExists('project_milestones');

        Schema::table('study_sessions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'scheduled_start']);
            $table->dropForeign(['topic_id']);
            $table->dropIndex(['topic_id', 'status']);
            $table->dropColumn([
                'topic_id',
                'scheduled_start',
                'scheduled_end',
                'actual_minutes',
                'target_description',
                'priority',
                'started_at',
            ]);
        });

        Schema::dropIfExists('study_goals');
    }
};
