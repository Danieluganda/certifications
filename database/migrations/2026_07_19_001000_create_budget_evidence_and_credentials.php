<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('scope_markdown')->nullable()->after('business_problem');
            $table->text('repository_url')->nullable()->after('deliverables');
            $table->text('demo_url')->nullable()->after('repository_url');
            $table->boolean('is_required')->default(true)->after('status');
            $table->date('target_date')->nullable()->after('is_required');
            $table->dateTime('completed_at')->nullable()->after('target_date');
            $table->text('review_notes')->nullable()->after('completed_at');
        });

        Schema::create('savings_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('amount_minor');
            $table->char('currency', 3);
            $table->string('transaction_type')->default('saving');
            $table->date('transaction_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'certification_id', 'transaction_date'], 'savings_user_cert_date_idx');
        });

        Schema::create('credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
            $table->string('credential_name');
            $table->string('provider_name');
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('credential_id')->nullable();
            $table->text('verification_url')->nullable();
            $table->text('certificate_file_path')->nullable();
            $table->text('badge_image_path')->nullable();
            $table->boolean('linkedin_added')->default(false);
            $table->boolean('cv_added')->default(false);
            $table->date('renewal_reminder_date')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'expiry_date'], 'credentials_user_expiry_idx');
        });

        Schema::create('evidence_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('evidenceable');
            $table->text('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_files');
        Schema::dropIfExists('credentials');
        Schema::dropIfExists('savings_transactions');

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['scope_markdown', 'repository_url', 'demo_url', 'is_required', 'target_date', 'completed_at', 'review_notes']);
        });
    }
};
