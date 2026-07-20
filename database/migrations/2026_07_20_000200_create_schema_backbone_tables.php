<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('weekly_availabilities')) {
            Schema::create('weekly_availabilities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->unsignedTinyInteger('day_of_week');
                $table->time('start_time');
                $table->time('end_time');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['user_id', 'day_of_week', 'start_time', 'end_time'], 'weekly_availability_unique_slot');
                $table->index(['user_id', 'is_active'], 'weekly_availability_user_active_idx');
            });
        }

        if (! Schema::hasTable('certification_objective_versions')) {
            Schema::create('certification_objective_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
                $table->string('version_label');
                $table->text('source_url')->nullable();
                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();
                $table->boolean('is_current')->default(false);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['certification_id', 'is_current'], 'cert_objective_current_idx');
            });
        }

        Schema::table('certification_domains', function (Blueprint $table) {
            if (! Schema::hasColumn('certification_domains', 'objective_version_id')) {
                $table->foreignId('objective_version_id')->nullable()->after('certification_id')->constrained('certification_objective_versions')->nullOnDelete();
            }
            if (! Schema::hasColumn('certification_domains', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (! Schema::hasColumn('certification_domains', 'is_major')) {
                $table->boolean('is_major')->default(true)->after('position');
            }
        });

        if (! Schema::hasTable('topic_prerequisites')) {
            Schema::create('topic_prerequisites', function (Blueprint $table) {
                $table->foreignId('topic_id')->constrained('topics')->cascadeOnDelete();
                $table->foreignId('prerequisite_topic_id')->constrained('topics')->cascadeOnDelete();

                $table->primary(['topic_id', 'prerequisite_topic_id'], 'topic_prerequisites_primary');
            });
        }

        if (! Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('slug');
                $table->timestamps();

                $table->unique(['user_id', 'slug']);
            });
        }

        if (! Schema::hasTable('taggables')) {
            Schema::create('taggables', function (Blueprint $table) {
                $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
                $table->morphs('taggable');

                $table->primary(['tag_id', 'taggable_type', 'taggable_id'], 'taggables_primary');
            });
        }

        if (! Schema::hasTable('study_plans')) {
            Schema::create('study_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->date('starts_on');
                $table->date('ends_on');
                $table->string('status')->default('Draft');
                $table->string('generated_by')->default('Manual');
                $table->json('generation_context')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status'], 'study_plans_user_status_idx');
                $table->index(['user_id', 'starts_on', 'ends_on'], 'study_plans_user_dates_idx');
            });
        }

        Schema::table('study_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('study_sessions', 'study_plan_id')) {
                $table->foreignId('study_plan_id')->nullable()->after('user_id')->constrained('study_plans')->nullOnDelete();
            }
            if (! Schema::hasColumn('study_sessions', 'priority_score')) {
                $table->decimal('priority_score', 8, 2)->nullable()->after('priority');
            }
            if (! Schema::hasColumn('study_sessions', 'confidence')) {
                $table->unsignedTinyInteger('confidence')->nullable()->after('completed_at');
            }
        });

        if (! Schema::hasTable('study_session_events')) {
            Schema::create('study_session_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('study_session_id')->constrained()->cascadeOnDelete();
                $table->string('event_type');
                $table->dateTime('occurred_at');
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['study_session_id', 'occurred_at'], 'study_session_events_session_time_idx');
            });
        }

        if (! Schema::hasTable('quiz_blueprints')) {
            Schema::create('quiz_blueprints', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('mode')->default('topic');
                $table->unsignedInteger('question_count');
                $table->unsignedInteger('duration_minutes')->nullable();
                $table->decimal('passing_score', 5, 2)->nullable();
                $table->json('configuration')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'certification_id', 'mode'], 'quiz_blueprints_user_cert_mode_idx');
            });
        }

        Schema::table('quiz_attempts', function (Blueprint $table) {
            if (! Schema::hasColumn('quiz_attempts', 'blueprint_id')) {
                $table->foreignId('blueprint_id')->nullable()->after('certification_id')->constrained('quiz_blueprints')->nullOnDelete();
            }
        });

        if (! Schema::hasTable('labs')) {
            Schema::create('labs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
                $table->foreignId('topic_id')->nullable()->constrained('topics')->nullOnDelete();
                $table->string('title');
                $table->text('objective');
                $table->longText('instructions_markdown');
                $table->text('expected_outcome')->nullable();
                $table->unsignedInteger('estimated_minutes')->nullable();
                $table->boolean('is_required')->default(true);
                $table->string('status')->default('Planned');
                $table->dateTime('completed_at')->nullable();
                $table->text('reflection')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status'], 'labs_user_status_idx');
                $table->index(['certification_id', 'topic_id'], 'labs_cert_topic_idx');
            });
        }

        if (! Schema::hasTable('exam_budgets')) {
            Schema::create('exam_budgets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('target_amount_minor');
                $table->char('currency', 3);
                $table->date('target_date')->nullable();
                $table->string('status')->default('Saving');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status'], 'exam_budgets_user_status_idx');
                $table->unique(['certification_id', 'status'], 'exam_budgets_cert_status_unique');
            });
        }

        Schema::table('savings_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('savings_transactions', 'exam_budget_id')) {
                $table->foreignId('exam_budget_id')->nullable()->after('certification_id')->constrained('exam_budgets')->nullOnDelete();
            }
        });

        if (! Schema::hasTable('vouchers')) {
            Schema::create('vouchers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
                $table->text('code_encrypted')->nullable();
                $table->string('discount_type');
                $table->decimal('discount_value', 8, 2)->nullable();
                $table->char('currency', 3)->nullable();
                $table->dateTime('expires_at')->nullable();
                $table->string('status')->default('Available');
                $table->string('source')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status', 'expires_at'], 'vouchers_user_status_expires_idx');
            });
        }

        if (! Schema::hasTable('progress_snapshots')) {
            Schema::create('progress_snapshots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('certification_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('period_type');
                $table->date('period_start');
                $table->date('period_end');
                $table->unsignedInteger('planned_minutes')->default(0);
                $table->unsignedInteger('completed_minutes')->default(0);
                $table->unsignedInteger('sessions_completed')->default(0);
                $table->unsignedInteger('quizzes_completed')->default(0);
                $table->decimal('average_score', 5, 2)->nullable();
                $table->unsignedInteger('reviews_completed')->default(0);
                $table->json('snapshot_data')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'period_type', 'period_start'], 'progress_snapshots_user_period_idx');
            });
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('type');
                $table->string('title');
                $table->text('message');
                $table->text('action_url')->nullable();
                $table->dateTime('read_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'read_at'], 'notifications_user_read_idx');
            });
        }

        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action');
                $table->string('auditable_type');
                $table->unsignedBigInteger('auditable_id')->nullable();
                $table->json('before_data')->nullable();
                $table->json('after_data')->nullable();
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'action'], 'audit_logs_user_action_idx');
                $table->index(['auditable_type', 'auditable_id'], 'audit_logs_auditable_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('progress_snapshots');
        Schema::dropIfExists('vouchers');

        Schema::table('savings_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exam_budget_id');
        });

        Schema::dropIfExists('exam_budgets');
        Schema::dropIfExists('labs');

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('blueprint_id');
        });

        Schema::dropIfExists('quiz_blueprints');
        Schema::dropIfExists('study_session_events');

        Schema::table('study_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('study_plan_id');
            $table->dropColumn(['priority_score', 'confidence']);
        });

        Schema::dropIfExists('study_plans');
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('topic_prerequisites');

        Schema::table('certification_domains', function (Blueprint $table) {
            $table->dropConstrainedForeignId('objective_version_id');
            $table->dropColumn(['description', 'is_major']);
        });

        Schema::dropIfExists('certification_objective_versions');
        Schema::dropIfExists('weekly_availabilities');
    }
};
