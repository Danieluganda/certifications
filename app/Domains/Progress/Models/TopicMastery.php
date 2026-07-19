<?php

namespace App\Domains\Progress\Models;

use App\Domains\Curriculum\Models\Topic;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicMastery extends Model
{
    protected $table = 'topic_mastery';

    protected $fillable = [
        'user_id', 'topic_id', 'mastery_percent', 'quiz_component', 'review_component',
        'lesson_component', 'lab_component', 'confidence_component', 'calculated_at',
        'calculation_version',
    ];

    protected function casts(): array
    {
        return ['calculated_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function topic(): BelongsTo { return $this->belongsTo(Topic::class); }
}
