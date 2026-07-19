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
        'title',
        'resource_type',
        'provider_name',
        'url',
        'trust_level',
        'copyright_status',
        'status',
    ];

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }
}
