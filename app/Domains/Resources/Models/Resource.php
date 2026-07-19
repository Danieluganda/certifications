<?php

namespace App\Domains\Resources\Models;

use App\Domains\Certifications\Models\Certification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resource extends Model
{
    protected $fillable = [
        'user_id',
        'certification_id',
        'domain_id',
        'topic_id',
        'title',
        'resource_type',
        'provider_name',
        'url',
        'file_path',
        'trust_level',
        'copyright_status',
        'copyright_note',
        'status',
        'rating',
    ];

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Curriculum\Models\CertificationDomain::class, 'domain_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Curriculum\Models\Topic::class);
    }
}
