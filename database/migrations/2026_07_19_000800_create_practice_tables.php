<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('domain_id')->constrained('certification_domains')->cascadeOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained('topics')->nullOnDelete();
            $table->string('question_type')->default('single_choice');
            $table->string('difficulty')->default('medium');
            $table->string('status')->default('active');
            $table->string('source_type')->default('lesson');
            $table->text('source_reference')->nullable();
            $table->unsignedInteger('current_version')->default(1);
            $table->timestamps();

            $table->index(['user_id', 'certification_id', 'status']);
            $table->index(['topic_id', 'status']);
        });

        Schema::create('question_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->text('prompt_markdown');
            $table->text('explanation_markdown')->nullable();
            $table->json('answer_schema')->nullable();
            $table->timestamps();

            $table->unique(['question_id', 'version_number']);
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_version_id')->constrained()->cascadeOnDelete();
            $table->string('option_key', 8);
            $table->text('body_markdown');
            $table->boolean('is_correct')->default(false);
            $table->text('explanation_markdown')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
            $table->string('attempt_type');
            $table->string('status')->default('In_progress');
            $table->dateTime('started_at');
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->decimal('score_percent', 5, 2)->nullable();
            $table->boolean('passed')->nullable();
            $table->unsignedInteger('total_questions');
            $table->unsignedInteger('correct_count')->nullable();
            $table->unsignedInteger('incorrect_count')->nullable();
            $table->unsignedInteger('unanswered_count')->nullable();
            $table->unsignedInteger('time_used_seconds')->nullable();
            $table->json('configuration_snapshot')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'certification_id', 'status']);
        });

        Schema::create('attempt_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_version_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->decimal('points_possible', 5, 2)->default(1);
            $table->decimal('points_awarded', 5, 2)->nullable();
            $table->boolean('is_correct')->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->unsignedInteger('response_time_seconds')->nullable();
            $table->timestamps();
        });

        Schema::create('attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('selected_option_id')->nullable()->constrained('question_options')->nullOnDelete();
            $table->text('answer_text')->nullable();
            $table->json('answer_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('attempt_domain_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('domain_id')->constrained('certification_domains')->cascadeOnDelete();
            $table->decimal('score_percent', 5, 2);
            $table->unsignedInteger('correct_count');
            $table->unsignedInteger('total_count');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempt_domain_scores');
        Schema::dropIfExists('attempt_answers');
        Schema::dropIfExists('attempt_questions');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('question_versions');
        Schema::dropIfExists('questions');
    }
};
