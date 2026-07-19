<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreResource extends Controller
{
    public function __invoke(Request $request, string $certificationSlug): RedirectResponse
    {
        $data = $request->validate([
            'domain_id' => ['nullable', 'integer'],
            'topic_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:180'],
            'resource_type' => ['required', Rule::in([
                'Official documentation',
                'Official learning path',
                'Video',
                'Article',
                'Book',
                'PDF',
                'Practice lab',
                'Practice test',
                'Community discussion',
                'Personal note',
            ])],
            'provider_name' => ['required', 'string', 'max:120'],
            'url' => ['nullable', 'url', 'max:2000'],
            'file_path' => ['nullable', 'string', 'max:2000'],
            'trust_level' => ['required', Rule::in(['Official', 'verified', 'community', 'personal'])],
            'copyright_status' => ['required', 'string', 'max:120'],
            'copyright_note' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['Not started', 'in progress', 'completed'])],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        if (empty($data['url']) && empty($data['file_path'])) {
            return back()->withErrors(['resource' => 'Add either a URL or a file path.']);
        }

        $certification = $request->user()
            ->certifications()
            ->where('slug', $certificationSlug)
            ->firstOrFail();

        Gate::authorize('view', $certification);

        $domainId = null;
        if (! empty($data['domain_id'])) {
            $domainId = $certification->domains()
                ->whereKey($data['domain_id'])
                ->firstOrFail()
                ->id;
        }

        $topicId = null;
        if (! empty($data['topic_id'])) {
            $topic = $certification->topics()
                ->whereKey($data['topic_id'])
                ->firstOrFail();

            if ($domainId && $topic->domain_id !== $domainId) {
                return back()->withErrors(['resource' => 'The selected topic must belong to the selected domain.']);
            }

            $topicId = $topic->id;
            $domainId ??= $topic->domain_id;
        }

        $certification->resources()->create([
            'user_id' => $request->user()->id,
            'domain_id' => $domainId,
            'topic_id' => $topicId,
            'title' => $data['title'],
            'resource_type' => $data['resource_type'],
            'provider_name' => $data['provider_name'],
            'url' => $data['url'] ?? null,
            'file_path' => $data['file_path'] ?? null,
            'trust_level' => $data['trust_level'],
            'copyright_status' => $data['copyright_status'],
            'copyright_note' => $data['copyright_note'] ?? null,
            'status' => $data['status'],
            'rating' => $data['rating'] ?? null,
        ]);

        return redirect()
            ->route('certifications.show', ['certificationSlug' => $certification->slug])
            ->withFragment('resources')
            ->with('status', 'Resource added.');
    }
}
