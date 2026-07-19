<?php

namespace App\Domains\Flashcards\Models;

use App\Domains\Curriculum\Models\Topic;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flashcard extends Model
{
    protected $fillable = [
        'user_id',
        'topic_id',
        'front',
        'back',
        'source_type',
        'source_reference',
        'status',
        'current_interval_days',
        'ease_factor',
        'next_review_at',
        'last_reviewed_at',
        'review_count',
        'lapse_count',
    ];

    protected function casts(): array
    {
        return [
            'ease_factor' => 'decimal:2',
            'next_review_at' => 'datetime',
            'last_reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(FlashcardReview::class);
    }
}
