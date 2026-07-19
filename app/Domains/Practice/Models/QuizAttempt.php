<?php

namespace App\Domains\Practice\Models;

use App\Domains\Certifications\Models\Certification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    protected $fillable = [
        'user_id', 'certification_id', 'blueprint_id', 'attempt_type', 'status', 'started_at', 'submitted_at',
        'expires_at', 'score_percent', 'passed', 'total_questions', 'correct_count',
        'incorrect_count', 'unanswered_count', 'time_used_seconds', 'configuration_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'expires_at' => 'datetime',
            'passed' => 'boolean',
            'configuration_snapshot' => 'array',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function certification(): BelongsTo { return $this->belongsTo(Certification::class); }
    public function blueprint(): BelongsTo { return $this->belongsTo(QuizBlueprint::class, 'blueprint_id'); }
    public function questions(): HasMany { return $this->hasMany(AttemptQuestion::class)->orderBy('position'); }
    public function domainScores(): HasMany { return $this->hasMany(AttemptDomainScore::class); }
}
