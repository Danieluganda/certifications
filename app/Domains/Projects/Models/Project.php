<?php

namespace App\Domains\Projects\Models;

use App\Domains\Certifications\Models\Certification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'certification_id',
        'title',
        'business_problem',
        'skills',
        'deliverables',
        'next_milestone',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'deliverables' => 'array',
        ];
    }

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }
}
