<?php

namespace App\Http\Controllers\Flashcards;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreFlashcard extends Controller
{
    public function __invoke(Request $request, string $certificationSlug): RedirectResponse
    {
        $data = $request->validate([
            'topic_id' => ['required', 'integer'],
            'front' => ['required', 'string', 'max:5000'],
            'back' => ['required', 'string', 'max:5000'],
            'source_type' => ['required', Rule::in(['Manual', 'lesson', 'AI'])],
            'source_reference' => ['nullable', 'string', 'max:2000'],
        ]);

        $certification = $request->user()
            ->certifications()
            ->where('slug', $certificationSlug)
            ->firstOrFail();

        Gate::authorize('view', $certification);

        $topic = $certification->topics()
            ->whereKey($data['topic_id'])
            ->firstOrFail();

        $topic->flashcards()->create([
            'user_id' => $request->user()->id,
            'front' => $data['front'],
            'back' => $data['back'],
            'source_type' => $data['source_type'],
            'source_reference' => $data['source_reference'] ?? null,
            'next_review_at' => now(),
        ]);

        return redirect()
            ->route('certifications.show', ['certificationSlug' => $certification->slug])
            ->withFragment('flashcards')
            ->with('status', 'Flashcard created.');
    }
}
