<?php

namespace App\Http\Controllers\Study;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StoreLessonNote extends Controller
{
    public function __invoke(Request $request, string $certificationSlug, int $lesson): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body_markdown' => ['required', 'string', 'max:10000'],
            'is_favourite' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $certification = $user->certifications()
            ->where('slug', $certificationSlug)
            ->firstOrFail();

        Gate::authorize('view', $certification);

        $lessonModel = $certification->lessons()
            ->whereKey($lesson)
            ->firstOrFail();

        $lessonModel->notes()->create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'body_markdown' => $data['body_markdown'],
            'is_favourite' => $request->boolean('is_favourite'),
        ]);

        return redirect()
            ->to(route('certifications.show', [
                'certificationSlug' => $certification->slug,
                'lesson' => $lessonModel->external_id,
            ]).'#notes')
            ->with('status', 'Note saved.');
    }
}
