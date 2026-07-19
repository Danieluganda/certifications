<?php

namespace App\Domains\Planning\Models;

use App\Domains\Certifications\Models\Certification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlannerRecommendation extends Model
{
    protected $fillable = [
        'user_id',
        'certification_id',
        'recommendation_type',
        'reason',
        'recommended_date',
        'duration_minutes',
        'priority',
        'accepted_at',
        'dismissed_at',
    ];

    protected function casts(): array
    {
        return [
            'recommended_date' => 'date',
            'accepted_at' => 'datetime',
            'dismissed_at' => 'datetime',
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
