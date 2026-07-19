<?php

namespace App\Domains\Practice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AttemptQuestion extends Model
{
    protected $fillable = [
        'question_id', 'question_version_id', 'position', 'points_possible', 'points_awarded',
        'is_correct', 'is_flagged', 'response_time_seconds',
    ];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean', 'is_flagged' => 'boolean'];
    }

    public function attempt(): BelongsTo { return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id'); }
    public function question(): BelongsTo { return $this->belongsTo(Question::class); }
    public function version(): BelongsTo { return $this->belongsTo(QuestionVersion::class, 'question_version_id'); }
    public function answer(): HasOne { return $this->hasOne(AttemptAnswer::class); }
}
