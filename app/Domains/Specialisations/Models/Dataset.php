<?php

namespace App\Domains\Specialisations\Models;

use App\Domains\Certifications\Models\Certification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dataset extends Model
{
    protected $fillable = [
        'user_id', 'certification_id', 'specialisation_id', 'name', 'dataset_type',
        'source_url', 'licence', 'description', 'storage_path', 'schema_metadata_json',
        'last_verified_at',
    ];

    protected function casts(): array
    {
        return ['schema_metadata_json' => 'array', 'last_verified_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function certification(): BelongsTo { return $this->belongsTo(Certification::class); }
    public function specialisation(): BelongsTo { return $this->belongsTo(Specialisation::class); }
}
