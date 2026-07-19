<?php

namespace App\Domains\Progress\Models;

use App\Domains\Certifications\Models\Certification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressSnapshot extends Model
{
    protected $fillable = [
        'user_id', 'certification_id', 'period_type', 'period_start', 'period_end',
        'planned_minutes', 'completed_minutes', 'sessions_completed',
        'quizzes_completed', 'average_score', 'reviews_completed', 'snapshot_data',
    ];

    protected function casts(): array
    {
        return ['period_start' => 'date', 'period_end' => 'date', 'snapshot_data' => 'array'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function certification(): BelongsTo { return $this->belongsTo(Certification::class); }
}
