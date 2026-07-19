<?php

namespace App\Domains\Curriculum\Models;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Flashcards\Models\Flashcard;
use App\Domains\Notes\Models\Note;
use App\Domains\Practice\Models\Question;
use App\Domains\Progress\Models\TopicMastery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Topic extends Model
{
    protected $fillable = [
        'certification_id',
        'domain_id',
        'name',
        'prerequisites',
        'position',
        'mastery_percent',
    ];

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(CertificationDomain::class, 'domain_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function mastery(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TopicMastery::class);
    }
}
