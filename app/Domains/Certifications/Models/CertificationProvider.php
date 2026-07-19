<?php

namespace App\Domains\Certifications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificationProvider extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'website_url',
    ];

    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class, 'provider_id');
    }
}
