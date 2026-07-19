<?php

namespace App\Domains\Curriculum\Models;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Notes\Models\Note;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lesson extends Model
{
    protected $fillable = [
        'certification_id',
        'domain_id',
        'external_id',
        'topic_name',
        'title',
        'summary',
        'body_markdown',
        'example_markdown',
        'exercise_markdown',
        'quiz_payload',
        'reference_payload',
        'proof_task',
        'estimated_minutes',
        'position',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quiz_payload' => 'array',
            'reference_payload' => 'array',
        ];
    }

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(CertificationDomain::class, 'domain_id');
    }

    public function completions(): HasMany
    {
        return $this->hasMany(LessonCompletion::class);
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }
}
