<?php

namespace App\Domains\Specialisations\Models;

use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchIndex extends Model
{
    protected $table = 'search_indexes';

    protected $fillable = [
        'user_id', 'project_id', 'engine', 'index_name', 'status',
        'document_count', 'configuration_json', 'last_indexed_at',
    ];

    protected function casts(): array
    {
        return ['configuration_json' => 'array', 'last_indexed_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
}
