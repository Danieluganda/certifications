<?php

namespace App\Domains\Specialisations\Models;

use App\Domains\Certifications\Models\Certification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialisation extends Model
{
    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'status', 'priority', 'target_completion_date',
    ];

    protected function casts(): array
    {
        return ['target_completion_date' => 'date'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function certifications(): BelongsToMany { return $this->belongsToMany(Certification::class); }
    public function datasets(): HasMany { return $this->hasMany(Dataset::class); }
    public function ontologyResources(): HasMany { return $this->hasMany(OntologyResource::class); }
}
