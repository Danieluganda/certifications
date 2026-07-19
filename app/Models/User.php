<?php

namespace App\Models;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Flashcards\Models\Flashcard;
use App\Domains\Notes\Models\Note;
use App\Domains\Planning\Models\PlannerRecommendation;
use App\Domains\Planning\Models\StudyGoal;
use App\Domains\Planning\Models\StudySession;
use App\Domains\Planning\Models\StudyStreak;
use App\Domains\Practice\Models\QuizAttempt;
use App\Domains\Projects\Models\ProjectMilestone;
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
}
