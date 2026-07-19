<?php

namespace App\Domains\Progress\Models;

use App\Domains\Certifications\Models\Certification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadinessSnapshot extends Model
{
    protected $fillable = [
        'user_id', 'certification_id', 'readiness_percent', 'status_label',
        'topic_quiz_component', 'mock_exam_component', 'domain_mastery_component',
        'lab_component', 'project_component', 'revision_component', 'guard_conditions',
        'calculated_at', 'calculation_version',
    ];

    protected function casts(): array
    {
        return ['guard_conditions' => 'array', 'calculated_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function certification(): BelongsTo { return $this->belongsTo(Certification::class); }
}
