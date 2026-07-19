<?php

namespace App\Domains\Practice\Models;

use App\Domains\Curriculum\Models\CertificationDomain;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttemptDomainScore extends Model
{
    protected $fillable = ['domain_id', 'score_percent', 'correct_count', 'total_count'];

    public function attempt(): BelongsTo { return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id'); }
    public function domain(): BelongsTo { return $this->belongsTo(CertificationDomain::class, 'domain_id'); }
}
