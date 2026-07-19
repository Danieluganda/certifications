<?php

namespace App\Http\Controllers\Progress;

use App\Domains\Progress\Actions\CalculateReadiness;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CalculateReadinessController extends Controller
{
    public function __invoke(Request $request, string $certificationSlug, CalculateReadiness $calculateReadiness): RedirectResponse
    {
        $certification = $request->user()->certifications()->where('slug', $certificationSlug)->firstOrFail();
        Gate::authorize('view', $certification);

        $calculateReadiness->execute($request->user(), $certification);

        return redirect()
            ->route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'readiness'])
            ->with('status', 'Readiness recalculated.');
    }
}
