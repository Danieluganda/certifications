<?php

namespace App\Models;

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Budgeting\Models\ExamBudget;
use App\Domains\Budgeting\Models\Voucher;
use App\Domains\Certifications\Models\Certification;
use App\Domains\Curriculum\Models\Lab;
use App\Domains\Flashcards\Models\Flashcard;
use App\Domains\Notes\Models\Note;
use App\Domains\Notifications\Models\AppNotification;
use App\Domains\Planning\Models\PlannerRecommendation;
use App\Domains\Planning\Models\StudyPlan;
use App\Domains\Planning\Models\StudyGoal;
use App\Domains\Planning\Models\StudySession;
use App\Domains\Planning\Models\StudyStreak;
use App\Domains\Planning\Models\WeeklyAvailability;
use App\Domains\Practice\Models\QuizAttempt;
use App\Domains\Projects\Models\ProjectMilestone;
use App\Domains\Progress\Models\ProgressSnapshot;
use App\Domains\Specialisations\Models\AnalyticsProperty;
use App\Domains\Specialisations\Models\Dataset;
use App\Domains\Specialisations\Models\OntologyResource;
use App\Domains\Specialisations\Models\SearchIndex;
use App\Domains\Specialisations\Models\Specialisation;
use App\Domains\Tutor\Models\LearnerMisconception;
use App\Domains\Tutor\Models\TutorFeedback;
use App\Domains\Tutor\Models\TutorRecommendation;
use App\Domains\Tutor\Models\TutorSession;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class);
    }

    public function studySessions(): HasMany
    {
        return $this->hasMany(StudySession::class);
    }

    public function weeklyAvailabilities(): HasMany
    {
        return $this->hasMany(WeeklyAvailability::class);
    }

    public function studyPlans(): HasMany
    {
        return $this->hasMany(StudyPlan::class);
    }

    public function studyGoals(): HasMany
    {
        return $this->hasMany(StudyGoal::class);
    }

    public function studyStreak(): HasOne
    {
        return $this->hasOne(StudyStreak::class);
    }

    public function plannerRecommendations(): HasMany
    {
        return $this->hasMany(PlannerRecommendation::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
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

    public function progressSnapshots(): HasMany
    {
        return $this->hasMany(ProgressSnapshot::class);
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function projectMilestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
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

    public function tutorFeedback(): HasMany
    {
        return $this->hasMany(TutorFeedback::class);
    }

    public function specialisations(): HasMany
    {
        return $this->hasMany(Specialisation::class);
    }

    public function datasets(): HasMany
    {
        return $this->hasMany(Dataset::class);
    }

    public function ontologyResources(): HasMany
    {
        return $this->hasMany(OntologyResource::class);
    }

    public function searchIndexes(): HasMany
    {
        return $this->hasMany(SearchIndex::class);
    }

    public function analyticsProperties(): HasMany
    {
        return $this->hasMany(AnalyticsProperty::class);
    }
}
