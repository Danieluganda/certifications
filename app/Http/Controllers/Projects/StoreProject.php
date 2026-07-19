<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StoreProject extends Controller
{
    public function __invoke(Request $request, string $certificationSlug): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'business_problem' => ['required', 'string', 'max:5000'],
            'scope_markdown' => ['nullable', 'string', 'max:10000'],
            'repository_url' => ['nullable', 'url', 'max:2000'],
            'demo_url' => ['nullable', 'url', 'max:2000'],
            'target_date' => ['nullable', 'date'],
        ]);

        $certification = $request->user()->certifications()->where('slug', $certificationSlug)->firstOrFail();
        Gate::authorize('view', $certification);

        $certification->projects()->create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'business_problem' => $data['business_problem'],
            'scope_markdown' => $data['scope_markdown'] ?? null,
            'repository_url' => $data['repository_url'] ?? null,
            'demo_url' => $data['demo_url'] ?? null,
            'target_date' => $data['target_date'] ?? null,
            'status' => 'Planned',
        ]);

        return redirect()->route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'projects'])->with('status', 'Project created.');
    }
}
