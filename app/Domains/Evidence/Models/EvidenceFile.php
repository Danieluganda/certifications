<?php

namespace App\Domains\Evidence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EvidenceFile extends Model
{
    protected $fillable = ['user_id', 'file_path', 'original_name', 'mime_type', 'size_bytes', 'description'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function evidenceable(): MorphTo { return $this->morphTo(); }
}
