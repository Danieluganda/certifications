<?php

namespace App\Http\Controllers\Curriculum;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StoreTopic extends Controller
{
    public function __invoke(Request $request, string $certificationSlug): RedirectResponse
    {
        $data = $request->validate([
            'domain_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:180'],
            'prerequisites' => ['nullable', 'string', 'max:2000'],
        ]);

        $certification = $request->user()
            ->certifications()
            ->where('slug', $certificationSlug)
            ->firstOrFail();

        Gate::authorize('view', $certification);

        $domain = $certification->domains()
            ->whereKey($data['domain_id'])
            ->firstOrFail();

        $domain->topics()->create([
            'certification_id' => $certification->id,
            'name' => $data['name'],
            'prerequisites' => $data['prerequisites'] ?? null,
            'position' => $domain->topics()->count() + 1,
        ]);

        return redirect()
            ->route('certifications.show', ['certificationSlug' => $certification->slug])
            ->withFragment('curriculum')
            ->with('status', 'Topic added.');
    }
}
