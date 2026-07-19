<?php

namespace App\Domains\Practice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttemptAnswer extends Model
{
    protected $fillable = ['selected_option_id', 'answer_text', 'answer_payload'];

    protected function casts(): array
    {
        return ['answer_payload' => 'array'];
    }

    public function attemptQuestion(): BelongsTo { return $this->belongsTo(AttemptQuestion::class); }
    public function selectedOption(): BelongsTo { return $this->belongsTo(QuestionOption::class, 'selected_option_id'); }
}
