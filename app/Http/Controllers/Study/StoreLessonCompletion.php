<?php

namespace App\Http\Controllers\Study;

use App\Domains\Certifications\Models\Certification;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreLessonCompletion extends Controller
{
    public function __invoke(Request $request, string $certificationSlug, int $lesson): RedirectResponse
    {
        $data = $request->validate([
            'confidence' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $user = $request->user();
        $certification = $user->certifications()
            ->where('slug', $certificationSlug)
            ->firstOrFail();

        Gate::authorize('view', $certification);

        $lessonModel = $certification->lessons()
            ->whereKey($lesson)
            ->firstOrFail();

        DB::transaction(function () use ($certification, $data, $lessonModel, $user): void {
            $lessonModel->completions()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'completed_at' => now(),
                    'confidence' => $data['confidence'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]
            );

            $totalLessons = max(1, $certification->lessons()->count());
            $completedLessons = $certification->lessons()
                ->whereHas('completions', fn ($query) => $query->where('user_id', $user->id))
                ->count();

            $certification->forceFill([
                'progress_percent' => (int) round(($completedLessons / $totalLessons) * 100),
            ])->save();
        });

        return redirect()
            ->to(route('certifications.show', [
                'certificationSlug' => $certification->slug,
                'workspacePage' => 'lesson',
                'lesson' => $lessonModel->external_id,
            ]))
            ->with('status', 'Lesson completion saved.');
    }
}
