<?php

namespace App\Domains\Specialisations\Models;

use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsProperty extends Model
{
    protected $fillable = [
        'user_id', 'project_id', 'provider', 'property_name',
        'property_identifier_encrypted', 'status', 'configuration_json',
    ];

    protected function casts(): array
    {
        return ['configuration_json' => 'array'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
}
