<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('timezone')->default('Africa/Kampala');
            $table->unsignedInteger('weekly_target_minutes')->default(0);
            $table->unsignedInteger('max_active_free_credentials')->default(2);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('certification_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('website_url')->nullable();
            $table->timestamps();
        });

        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained('certification_providers')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('exam_code')->nullable();
            $table->string('track_type');
            $table->string('status')->default('Planned');
            $table->unsignedTinyInteger('priority')->default(3);
            $table->boolean('is_primary')->default(false);
            $table->date('target_completion_date')->nullable();
            $table->unsignedInteger('weekly_minutes')->default(0);
            $table->unsignedTinyInteger('readiness_percent')->default(0);
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->unsignedBigInteger('exam_target_amount_minor')->nullable();
            $table->unsignedBigInteger('exam_saved_amount_minor')->nullable();
            $table->char('exam_currency', 3)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'slug']);
            $table->index(['user_id', 'track_type', 'status']);
            $table->index(['user_id', 'is_primary']);
        });

        Schema::create('certification_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('weight_percent', 5, 2)->nullable();
            $table->unsignedTinyInteger('mastery_percent')->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained('certification_domains')->nullOnDelete();
            $table->string('external_id')->nullable();
            $table->string('topic_name')->nullable();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->longText('body_markdown');
            $table->longText('example_markdown')->nullable();
            $table->longText('exercise_markdown')->nullable();
            $table->json('quiz_payload')->nullable();
            $table->json('reference_payload')->nullable();
            $table->text('proof_task')->nullable();
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['certification_id', 'external_id']);
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('business_problem');
            $table->json('skills')->nullable();
            $table->json('deliverables')->nullable();
            $table->string('next_milestone')->nullable();
            $table->string('status')->default('Planned');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('resource_type');
            $table->string('provider_name')->nullable();
            $table->text('url')->nullable();
            $table->string('trust_level')->default('personal');
            $table->string('copyright_status')->default('personal_notes_allowed');
            $table->string('status')->default('Not started');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('certification_domains');
        Schema::dropIfExists('certifications');
        Schema::dropIfExists('certification_providers');
        Schema::dropIfExists('user_profiles');
    }
};
