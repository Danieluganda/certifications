<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topic_mastery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->decimal('mastery_percent', 5, 2)->default(0);
            $table->decimal('quiz_component', 5, 2)->default(0);
            $table->decimal('review_component', 5, 2)->default(0);
            $table->decimal('lesson_component', 5, 2)->default(0);
            $table->decimal('lab_component', 5, 2)->default(0);
            $table->decimal('confidence_component', 5, 2)->default(0);
            $table->dateTime('calculated_at');
            $table->string('calculation_version')->default('mvp-1');
            $table->timestamps();

            $table->unique(['user_id', 'topic_id']);
        });

        Schema::create('readiness_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
            $table->decimal('readiness_percent', 5, 2);
            $table->string('status_label');
            $table->decimal('topic_quiz_component', 5, 2)->default(0);
            $table->decimal('mock_exam_component', 5, 2)->default(0);
            $table->decimal('domain_mastery_component', 5, 2)->default(0);
            $table->decimal('lab_component', 5, 2)->default(0);
            $table->decimal('project_component', 5, 2)->default(0);
            $table->decimal('revision_component', 5, 2)->default(0);
            $table->json('guard_conditions')->nullable();
            $table->dateTime('calculated_at');
            $table->string('calculation_version')->default('mvp-1');
            $table->timestamps();

            $table->index(['user_id', 'certification_id', 'calculated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('readiness_snapshots');
        Schema::dropIfExists('topic_mastery');
    }
};
