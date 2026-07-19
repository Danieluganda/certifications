<?php

namespace App\Domains\Planning\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyPlan extends Model
{
    protected $fillable = ['user_id', 'name', 'starts_on', 'ends_on', 'status', 'generated_by', 'generation_context'];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'generation_context' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(StudySession::class);
    }
}
