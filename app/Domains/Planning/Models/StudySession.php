<?php

namespace App\Domains\Planning\Models;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Curriculum\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudySession extends Model
{
    protected $fillable = [
        'user_id',
        'certification_id',
        'lesson_id',
        'activity_type',
        'scheduled_for',
        'planned_minutes',
        'status',
        'notes',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime',
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
}
