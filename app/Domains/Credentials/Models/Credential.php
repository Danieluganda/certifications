<?php

namespace App\Domains\Credentials\Models;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Evidence\Models\EvidenceFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Credential extends Model
{
    protected $fillable = [
        'user_id', 'certification_id', 'credential_name', 'provider_name', 'issue_date', 'expiry_date',
        'credential_id', 'verification_url', 'certificate_file_path', 'badge_image_path',
        'linkedin_added', 'cv_added', 'renewal_reminder_date',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiry_date' => 'date',
            'linkedin_added' => 'boolean',
            'cv_added' => 'boolean',
            'renewal_reminder_date' => 'date',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function certification(): BelongsTo { return $this->belongsTo(Certification::class); }
    public function evidenceFiles(): MorphMany { return $this->morphMany(EvidenceFile::class, 'evidenceable'); }
}
