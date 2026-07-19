<?php

namespace App\Domains\Planning\Models;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Curriculum\Models\Lesson;
use App\Domains\Curriculum\Models\Topic;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudySession extends Model
{
    protected $fillable = [
        'user_id',
        'study_plan_id',
        'certification_id',
        'lesson_id',
        'topic_id',
        'activity_type',
        'scheduled_start',
        'scheduled_end',
        'scheduled_for',
        'planned_minutes',
        'actual_minutes',
        'target_description',
        'priority',
        'priority_score',
        'status',
        'notes',
        'started_at',
        'completed_at',
        'confidence',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_start' => 'datetime',
            'scheduled_end' => 'datetime',
            'scheduled_for' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(SessionTask::class)->orderBy('position');
    }

    public function studyPlan(): BelongsTo
    {
        return $this->belongsTo(StudyPlan::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(StudySessionEvent::class)->orderBy('occurred_at');
    }
}
