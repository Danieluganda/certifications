<?php

namespace App\Http\Controllers\Budgeting;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreSavingsTransaction extends Controller
{
    public function __invoke(Request $request, string $certificationSlug): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'currency' => ['required', 'string', 'size:3'],
            'transaction_type' => ['required', Rule::in(['saving', 'withdrawal'])],
            'transaction_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $certification = $request->user()->certifications()->where('slug', $certificationSlug)->firstOrFail();
        Gate::authorize('view', $certification);

        $amountMinor = (int) round($data['amount'] * 100) * ($data['transaction_type'] === 'withdrawal' ? -1 : 1);

        DB::transaction(function () use ($amountMinor, $certification, $data, $request): void {
            $certification->savingsTransactions()->create([
                'user_id' => $request->user()->id,
                'amount_minor' => $amountMinor,
                'currency' => strtoupper($data['currency']),
                'transaction_type' => $data['transaction_type'],
                'transaction_date' => $data['transaction_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            $certification->forceFill([
                'exam_currency' => strtoupper($data['currency']),
                'exam_saved_amount_minor' => max(0, (int) $certification->exam_saved_amount_minor + $amountMinor),
            ])->save();
        });

        return redirect()->route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'budget'])->with('status', 'Savings updated.');
    }
}
