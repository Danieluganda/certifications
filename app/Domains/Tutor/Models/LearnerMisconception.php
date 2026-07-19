<?php

namespace App\Domains\Tutor\Models;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Curriculum\Models\Topic;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearnerMisconception extends Model
{
    protected $fillable = [
        'user_id',
        'certification_id',
        'topic_id',
        'description',
        'first_detected_at',
        'last_detected_at',
        'detection_count',
        'resolved_at',
        'evidence',
    ];

    protected function casts(): array
    {
        return [
            'first_detected_at' => 'datetime',
            'last_detected_at' => 'datetime',
            'resolved_at' => 'datetime',
            'evidence' => 'array',
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

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }
}
