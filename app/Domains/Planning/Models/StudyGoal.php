<?php

namespace App\Domains\Planning\Models;

use App\Domains\Certifications\Models\Certification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyGoal extends Model
{
    protected $fillable = [
        'user_id',
        'certification_id',
        'goal_period',
        'goal_type',
        'target_value',
        'current_value',
        'unit',
        'starts_on',
        'ends_on',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
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
}
