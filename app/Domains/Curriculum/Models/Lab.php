<?php

namespace App\Domains\Curriculum\Models;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Evidence\Models\EvidenceFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lab extends Model
{
    protected $fillable = [
        'user_id', 'certification_id', 'topic_id', 'title', 'objective',
        'instructions_markdown', 'expected_outcome', 'estimated_minutes',
        'is_required', 'status', 'completed_at', 'reflection',
    ];

    protected function casts(): array
    {
        return ['is_required' => 'boolean', 'completed_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function certification(): BelongsTo { return $this->belongsTo(Certification::class); }
    public function topic(): BelongsTo { return $this->belongsTo(Topic::class); }
    public function evidenceFiles(): MorphMany { return $this->morphMany(EvidenceFile::class, 'evidenceable'); }
}
