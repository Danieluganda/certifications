<?php

namespace App\Domains\Certifications\Models;

use App\Domains\Budgeting\Models\ExamBudget;
use App\Domains\Budgeting\Models\Voucher;
use App\Domains\Certifications\Enums\CertificationTrack;
use App\Domains\Certifications\Models\CertificationObjectiveVersion;
use App\Domains\Curriculum\Models\CertificationDomain;
use App\Domains\Curriculum\Models\Lab;
use App\Domains\Curriculum\Models\Lesson;
use App\Domains\Curriculum\Models\Topic;
use App\Domains\Planning\Models\StudySession;
use App\Domains\Planning\Models\StudyGoal;
use App\Domains\Planning\Models\PlannerRecommendation;
use App\Domains\Practice\Models\Question;
use App\Domains\Practice\Models\QuizAttempt;
use App\Domains\Practice\Models\QuizBlueprint;
use App\Domains\Progress\Models\ProgressSnapshot;
use App\Domains\Progress\Models\ReadinessSnapshot;
use App\Domains\Budgeting\Models\SavingsTransaction;
use App\Domains\Credentials\Models\Credential;
use App\Domains\Projects\Models\Project;
use App\Domains\Resources\Models\Resource;
use App\Domains\Tutor\Models\LearnerMisconception;
use App\Domains\Tutor\Models\TutorRecommendation;
use App\Domains\Tutor\Models\TutorSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Certification extends Model
{
    protected $fillable = [
        'user_id',
        'provider_id',
        'name',
        'slug',
        'exam_code',
        'track_type',
        'status',
        'priority',
        'is_primary',
        'target_completion_date',
        'weekly_minutes',
        'readiness_percent',
        'progress_percent',
        'exam_target_amount_minor',
        'exam_saved_amount_minor',
        'exam_currency',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'track_type' => CertificationTrack::class,
            'is_primary' => 'boolean',
            'target_completion_date' => 'date',
            'metadata' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(CertificationProvider::class, 'provider_id');
    }

    public function domains(): HasMany
    {
        return $this->hasMany(CertificationDomain::class);
    }

    public function objectiveVersions(): HasMany
    {
        return $this->hasMany(CertificationObjectiveVersion::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function studySessions(): HasMany
    {
        return $this->hasMany(StudySession::class);
    }

    public function studyGoals(): HasMany
    {
        return $this->hasMany(StudyGoal::class);
    }

    public function plannerRecommendations(): HasMany
    {
        return $this->hasMany(PlannerRecommendation::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function quizBlueprints(): HasMany
    {
        return $this->hasMany(QuizBlueprint::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function readinessSnapshots(): HasMany
    {
        return $this->hasMany(ReadinessSnapshot::class);
    }

    public function progressSnapshots(): HasMany
    {
        return $this->hasMany(ProgressSnapshot::class);
    }

    public function labs(): HasMany
    {
        return $this->hasMany(Lab::class);
    }

    public function examBudgets(): HasMany
    {
        return $this->hasMany(ExamBudget::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function savingsTransactions(): HasMany
    {
        return $this->hasMany(SavingsTransaction::class);
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(Credential::class);
    }

    public function tutorSessions(): HasMany
    {
        return $this->hasMany(TutorSession::class);
    }

    public function tutorRecommendations(): HasMany
    {
        return $this->hasMany(TutorRecommendation::class);
    }

    public function learnerMisconceptions(): HasMany
    {
        return $this->hasMany(LearnerMisconception::class);
    }
}
