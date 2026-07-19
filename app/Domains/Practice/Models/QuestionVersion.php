<?php

namespace App\Domains\Practice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionVersion extends Model
{
    protected $fillable = ['version_number', 'prompt_markdown', 'explanation_markdown', 'answer_schema'];

    protected function casts(): array
    {
        return ['answer_schema' => 'array'];
    }

    public function question(): BelongsTo { return $this->belongsTo(Question::class); }
    public function options(): HasMany { return $this->hasMany(QuestionOption::class)->orderBy('position'); }
}
