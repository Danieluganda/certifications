<?php

namespace App\Domains\Tutor\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorFeedback extends Model
{
    protected $table = 'tutor_feedback';

    protected $fillable = [
        'user_id',
        'tutor_message_id',
        'rating',
        'feedback',
        'was_helpful',
        'was_accurate',
    ];

    protected function casts(): array
    {
        return [
            'was_helpful' => 'boolean',
            'was_accurate' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(TutorMessage::class, 'tutor_message_id');
    }
}
