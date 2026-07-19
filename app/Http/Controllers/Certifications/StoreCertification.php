<?php

namespace App\Http\Controllers\Certifications;

use App\Domains\Certifications\Enums\CertificationTrack;
use App\Domains\Certifications\Models\CertificationProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCertification extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'provider_name' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:180'],
            'exam_code' => ['nullable', 'string', 'max:40'],
            'track_type' => ['required', Rule::enum(CertificationTrack::class)],
            'target_completion_date' => ['nullable', 'date'],
            'weekly_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
        ]);

        $provider = CertificationProvider::query()->firstOrCreate(
            ['slug' => Str::slug($data['provider_name'])],
            ['name' => $data['provider_name']]
        );

        $baseSlug = Str::slug(($data['exam_code'] ?? null) ?: $data['name']);
        $slug = $baseSlug;
        $suffix = 2;

        while ($request->user()->certifications()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        $request->user()->certifications()->create([
            'provider_id' => $provider->id,
            'name' => $data['name'],
            'slug' => $slug,
            'exam_code' => $data['exam_code'] ?? null,
            'track_type' => $data['track_type'],
            'status' => 'Planned',
            'priority' => 3,
            'target_completion_date' => $data['target_completion_date'] ?? null,
            'weekly_minutes' => $data['weekly_minutes'] ?? 0,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Certification added.');
    }
}
