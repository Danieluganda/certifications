<?php

namespace App\Domains\Tutor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TutorMessage extends Model
{
    protected $fillable = [
        'tutor_session_id',
        'role',
        'message',
        'source_references',
        'model_metadata',
    ];

    protected function casts(): array
    {
        return [
            'source_references' => 'array',
            'model_metadata' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(TutorSession::class, 'tutor_session_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(TutorFeedback::class);
    }
}
