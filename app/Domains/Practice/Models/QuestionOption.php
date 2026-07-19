<?php

namespace App\Domains\Practice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    protected $fillable = ['option_key', 'body_markdown', 'is_correct', 'explanation_markdown', 'position'];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean'];
    }

    public function version(): BelongsTo { return $this->belongsTo(QuestionVersion::class, 'question_version_id'); }
}
