<?php

namespace App\Http\Controllers\Certifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ShowCertification extends Controller
{
    public function __invoke(Request $request, string $certificationSlug): View
    {
        $certification = $request->user()
            ->certifications()
            ->where('slug', $certificationSlug)
            ->firstOrFail();

        Gate::authorize('view', $certification);

        $certification->load([
            'provider',
            'domains',
            'lessons.domain',
            'projects',
            'resources',
        ]);

        $selectedLesson = $certification->lessons
            ->firstWhere('external_id', $request->query('lesson'))
            ?? $certification->lessons->first();

        $completion = $selectedLesson?->completions()
            ->where('user_id', $request->user()->id)
            ->first();

        $notes = $selectedLesson?->notes()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->take(5)
            ->get() ?? collect();

        return view('certifications.show', [
            'certification' => $certification,
            'selectedLesson' => $selectedLesson,
            'completion' => $completion,
            'notes' => $notes,
        ]);
    }
}
