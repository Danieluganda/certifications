<?php

namespace App\Domains\Tutor\Models;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Curriculum\Models\Topic;
use App\Domains\Planning\Models\StudySession;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorRecommendation extends Model
{
    protected $fillable = [
        'user_id',
        'tutor_session_id',
        'certification_id',
        'topic_id',
        'recommendation_type',
        'title',
        'reason',
        'priority',
        'status',
        'scheduled_session_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(TutorSession::class, 'tutor_session_id');
    }

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function scheduledSession(): BelongsTo
    {
        return $this->belongsTo(StudySession::class, 'scheduled_session_id');
    }
}
