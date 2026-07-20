<?php

namespace App\Domains\Projects\Models;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Evidence\Models\EvidenceFile;
use App\Domains\Specialisations\Models\AnalyticsProperty;
use App\Domains\Specialisations\Models\SearchIndex;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'certification_id',
        'title',
        'business_problem',
        'scope_markdown',
        'skills',
        'deliverables',
        'repository_url',
        'demo_url',
        'next_milestone',
        'status',
        'is_required',
        'target_date',
        'completed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'deliverables' => 'array',
            'is_required' => 'boolean',
            'target_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    public function evidenceFiles(): MorphMany
    {
        return $this->morphMany(EvidenceFile::class, 'evidenceable');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class)->orderBy('position');
    }

    public function searchIndexes(): HasMany
    {
        return $this->hasMany(SearchIndex::class);
    }

    public function analyticsProperties(): HasMany
    {
        return $this->hasMany(AnalyticsProperty::class);
    }
}
