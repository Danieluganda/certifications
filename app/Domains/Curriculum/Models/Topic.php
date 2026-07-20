<?php

namespace App\Domains\Curriculum\Models;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Flashcards\Models\Flashcard;
use App\Domains\Notes\Models\Note;
use App\Domains\Planning\Models\SessionTask;
use App\Domains\Planning\Models\StudySession;
use App\Domains\Practice\Models\Question;
use App\Domains\Progress\Models\TopicMastery;
use App\Domains\Tutor\Models\LearnerMisconception;
use App\Domains\Tutor\Models\TutorRecommendation;
use App\Domains\Tutor\Models\TutorSession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Topic extends Model
{
    protected $fillable = [
        'certification_id',
        'domain_id',
        'name',
        'prerequisites',
        'position',
        'mastery_percent',
    ];

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(CertificationDomain::class, 'domain_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function labs(): HasMany
    {
        return $this->hasMany(Lab::class);
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function mastery(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TopicMastery::class);
    }

    public function studySessions(): HasMany
    {
        return $this->hasMany(StudySession::class);
    }

    public function sessionTasks(): HasMany
    {
        return $this->hasMany(SessionTask::class);
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
