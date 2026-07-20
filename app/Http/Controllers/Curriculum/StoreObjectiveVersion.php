<?php

namespace App\Http\Controllers\Curriculum;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StoreObjectiveVersion extends Controller
{
    public function __invoke(Request $request, string $certificationSlug): RedirectResponse
    {
        $data = $request->validate([
            'version_label' => ['required', 'string', 'max:180'],
            'source_url' => ['nullable', 'url', 'max:2000'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_current' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $certification = $request->user()
            ->certifications()
            ->where('slug', $certificationSlug)
            ->firstOrFail();

        Gate::authorize('view', $certification);

        if ($request->boolean('is_current')) {
            $certification->objectiveVersions()->update(['is_current' => false]);
        }

        $certification->objectiveVersions()->create([
            'version_label' => $data['version_label'],
            'source_url' => $data['source_url'] ?? null,
            'effective_from' => $data['effective_from'] ?? null,
            'effective_to' => $data['effective_to'] ?? null,
            'is_current' => $request->boolean('is_current'),
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'curriculum'])
            ->with('status', 'Objective version added.');
    }
}
