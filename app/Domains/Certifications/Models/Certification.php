<?php

namespace App\Domains\Certifications\Models;

use App\Domains\Certifications\Enums\CertificationTrack;
use App\Domains\Curriculum\Models\CertificationDomain;
use App\Domains\Curriculum\Models\Lesson;
use App\Domains\Curriculum\Models\Topic;
use App\Domains\Planning\Models\StudySession;
use App\Domains\Practice\Models\Question;
use App\Domains\Practice\Models\QuizAttempt;
use App\Domains\Projects\Models\Project;
use App\Domains\Resources\Models\Resource;
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

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
