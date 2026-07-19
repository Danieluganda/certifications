<?php

namespace App\Domains\Practice\Models;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Curriculum\Models\CertificationDomain;
use App\Domains\Curriculum\Models\Topic;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Question extends Model
{
    protected $fillable = [
        'user_id',
        'certification_id',
        'domain_id',
        'topic_id',
        'question_type',
        'difficulty',
        'status',
        'source_type',
        'source_reference',
        'current_version',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function certification(): BelongsTo { return $this->belongsTo(Certification::class); }
    public function domain(): BelongsTo { return $this->belongsTo(CertificationDomain::class, 'domain_id'); }
    public function topic(): BelongsTo { return $this->belongsTo(Topic::class); }
    public function versions(): HasMany { return $this->hasMany(QuestionVersion::class); }
    public function currentVersion(): HasOne { return $this->hasOne(QuestionVersion::class)->ofMany('version_number', 'max'); }
}
