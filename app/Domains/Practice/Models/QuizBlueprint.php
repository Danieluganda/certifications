<?php

namespace App\Domains\Practice\Models;

use App\Domains\Certifications\Models\Certification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizBlueprint extends Model
{
    protected $fillable = ['user_id', 'certification_id', 'name', 'mode', 'question_count', 'duration_minutes', 'passing_score', 'configuration'];

    protected function casts(): array
    {
        return ['configuration' => 'array'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function certification(): BelongsTo { return $this->belongsTo(Certification::class); }
    public function attempts(): HasMany { return $this->hasMany(QuizAttempt::class, 'blueprint_id'); }
}
