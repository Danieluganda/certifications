<?php

namespace App\Domains\Budgeting\Models;

use App\Domains\Certifications\Models\Certification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamBudget extends Model
{
    protected $fillable = ['user_id', 'certification_id', 'target_amount_minor', 'currency', 'target_date', 'status', 'notes'];

    protected function casts(): array
    {
        return ['target_date' => 'date'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function certification(): BelongsTo { return $this->belongsTo(Certification::class); }
    public function savingsTransactions(): HasMany { return $this->hasMany(SavingsTransaction::class); }
}
