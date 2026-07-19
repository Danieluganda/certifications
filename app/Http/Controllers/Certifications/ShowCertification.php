<?php

namespace App\Http\Controllers\Certifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ShowCertification extends Controller
{
    public function __invoke(Request $request, string $certificationSlug, string $workspacePage = 'overview'): View
    {
        abort_unless(in_array($workspacePage, ['overview', 'curriculum', 'lesson', 'practice', 'readiness', 'flashcards', 'budget', 'projects', 'credentials', 'resources'], true), 404);

        $certification = $request->user()
            ->certifications()
            ->where('slug', $certificationSlug)
            ->firstOrFail();

        Gate::authorize('view', $certification);

        $certification->load([
            'provider',
            'domains.topics',
            'lessons.domain',
            'lessons.topic',
            'projects.evidenceFiles',
            'resources.domain',
            'resources.topic',
            'questions',
            'credentials',
            'savingsTransactions',
        ]);

        $latestReadiness = $certification->readinessSnapshots()
            ->where('user_id', $request->user()->id)
            ->latest('calculated_at')
            ->first();

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
            'workspacePage' => $workspacePage,
            'selectedLesson' => $selectedLesson,
            'completion' => $completion,
            'latestReadiness' => $latestReadiness,
            'weakDomains' => $certification->domains()
                ->where('mastery_percent', '<', 70)
                ->orderBy('mastery_percent')
                ->get(),
            'recentAttempts' => $certification->quizAttempts()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->take(5)
                ->get(),
            'flashcards' => $certification->topics()
                ->with(['domain', 'flashcards' => fn ($query) => $query
                    ->where('user_id', $request->user()->id)
                    ->where('status', 'Active')
                    ->where(function ($query): void {
                        $query->whereNull('next_review_at')->orWhere('next_review_at', '<=', now());
                    })
                    ->orderBy('next_review_at')])
                ->get()
                ->flatMap->flashcards
                ->take(8),
            'notes' => $notes,
        ]);
    }
}
