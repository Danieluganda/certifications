<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flashcards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->text('front');
            $table->text('back');
            $table->string('source_type')->default('Manual');
            $table->text('source_reference')->nullable();
            $table->string('status')->default('Active');
            $table->unsignedInteger('current_interval_days')->default(0);
            $table->decimal('ease_factor', 4, 2)->default(2.50);
            $table->dateTime('next_review_at')->nullable();
            $table->dateTime('last_reviewed_at')->nullable();
            $table->unsignedInteger('review_count')->default(0);
            $table->unsignedInteger('lapse_count')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'status', 'next_review_at']);
        });

        Schema::create('flashcard_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flashcard_id')->constrained()->cascadeOnDelete();
            $table->string('rating');
            $table->unsignedTinyInteger('confidence')->nullable();
            $table->unsignedInteger('previous_interval_days');
            $table->unsignedInteger('next_interval_days');
            $table->dateTime('reviewed_at');
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'reviewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flashcard_reviews');
        Schema::dropIfExists('flashcards');
    }
};
