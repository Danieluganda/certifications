<?php

namespace App\Http\Controllers\Curriculum;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StoreDomain extends Controller
{
    public function __invoke(Request $request, string $certificationSlug): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'weight_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $certification = $request->user()
            ->certifications()
            ->where('slug', $certificationSlug)
            ->firstOrFail();

        Gate::authorize('view', $certification);

        $currentWeight = (float) $certification->domains()->sum('weight_percent');
        $newWeight = (float) ($data['weight_percent'] ?? 0);

        if ($newWeight > 0 && ($currentWeight + $newWeight) > 100) {
            return back()->withErrors(['domain' => 'Domain weights cannot exceed 100%.']);
        }

        $certification->domains()->create([
            'name' => $data['name'],
            'weight_percent' => $data['weight_percent'] ?? null,
            'position' => $certification->domains()->count() + 1,
        ]);

        return redirect()
            ->route('certifications.show', ['certificationSlug' => $certification->slug])
            ->withFragment('curriculum')
            ->with('status', 'Domain added.');
    }
}
