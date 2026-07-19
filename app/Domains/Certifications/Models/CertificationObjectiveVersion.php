<?php

namespace App\Domains\Certifications\Models;

use App\Domains\Curriculum\Models\CertificationDomain;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificationObjectiveVersion extends Model
{
    protected $fillable = ['certification_id', 'version_label', 'source_url', 'effective_from', 'effective_to', 'is_current', 'notes'];

    protected function casts(): array
    {
        return ['effective_from' => 'date', 'effective_to' => 'date', 'is_current' => 'boolean'];
    }

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(CertificationDomain::class, 'objective_version_id');
    }
}
