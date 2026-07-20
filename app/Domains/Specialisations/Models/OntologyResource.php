<?php

namespace App\Domains\Specialisations\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OntologyResource extends Model
{
    protected $fillable = [
        'user_id', 'specialisation_id', 'name', 'resource_type', 'namespace_uri',
        'source_url', 'version', 'licence', 'metadata_json',
    ];

    protected function casts(): array
    {
        return ['metadata_json' => 'array'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function specialisation(): BelongsTo { return $this->belongsTo(Specialisation::class); }
}
