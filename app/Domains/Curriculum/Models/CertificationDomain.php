<?php

namespace App\Domains\Curriculum\Models;

use App\Domains\Certifications\Models\Certification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificationDomain extends Model
{
    protected $fillable = [
        'certification_id',
        'name',
        'weight_percent',
        'mastery_percent',
        'position',
    ];

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'domain_id');
    }
}
