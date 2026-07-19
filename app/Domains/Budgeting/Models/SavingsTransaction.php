<?php

namespace App\Domains\Budgeting\Models;

use App\Domains\Certifications\Models\Certification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsTransaction extends Model
{
    protected $fillable = ['user_id', 'certification_id', 'amount_minor', 'currency', 'transaction_type', 'transaction_date', 'notes'];

    protected function casts(): array
    {
        return ['transaction_date' => 'date'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function certification(): BelongsTo { return $this->belongsTo(Certification::class); }
}
