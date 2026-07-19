<?php

namespace App\Domains\Tutor\Models;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Curriculum\Models\Topic;
use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TutorSession extends Model
{
    protected $fillable = [
        'user_id',
        'certification_id',
        'topic_id',
        'project_id',
        'mode',
        'title',
        'status',
        'started_at',
        'completed_at',
        'summary',
    ];

    protected function casts(): array
    {
        return [
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

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TutorMessage::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(TutorRecommendation::class);
    }
}
