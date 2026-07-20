<?php

namespace App\Http\Controllers\Curriculum;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StoreLab extends Controller
{
    public function __invoke(Request $request, string $certificationSlug): RedirectResponse
    {
        $data = $request->validate([
            'topic_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:180'],
            'objective' => ['required', 'string', 'max:3000'],
            'instructions_markdown' => ['required', 'string', 'max:12000'],
            'expected_outcome' => ['nullable', 'string', 'max:3000'],
            'estimated_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
            'is_required' => ['nullable', 'boolean'],
        ]);

        $certification = $request->user()
            ->certifications()
            ->where('slug', $certificationSlug)
            ->firstOrFail();

        Gate::authorize('view', $certification);

        $topicId = null;
        if (! empty($data['topic_id'])) {
            $topicId = $certification->topics()
                ->whereKey($data['topic_id'])
                ->firstOrFail()
                ->id;
        }

        $certification->labs()->create([
            'user_id' => $request->user()->id,
            'topic_id' => $topicId,
            'title' => $data['title'],
            'objective' => $data['objective'],
            'instructions_markdown' => $data['instructions_markdown'],
            'expected_outcome' => $data['expected_outcome'] ?? null,
            'estimated_minutes' => $data['estimated_minutes'] ?? null,
            'is_required' => $request->boolean('is_required', true),
            'status' => 'Planned',
        ]);

        return redirect()
            ->route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'labs'])
            ->with('status', 'Lab added.');
    }
}
