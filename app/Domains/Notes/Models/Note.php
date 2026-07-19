<?php

namespace App\Domains\Notes\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Note extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body_markdown',
        'is_favourite',
    ];

    protected function casts(): array
    {
        return [
            'is_favourite' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function noteable(): MorphTo
    {
        return $this->morphTo();
    }
}
