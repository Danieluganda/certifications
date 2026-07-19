<?php

namespace App\Http\Controllers\Credentials;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StoreCredential extends Controller
{
    public function __invoke(Request $request, string $certificationSlug): RedirectResponse
    {
        $data = $request->validate([
            'credential_name' => ['required', 'string', 'max:180'],
            'provider_name' => ['required', 'string', 'max:120'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'credential_id' => ['nullable', 'string', 'max:120'],
            'verification_url' => ['nullable', 'url', 'max:2000'],
            'linkedin_added' => ['nullable', 'boolean'],
            'cv_added' => ['nullable', 'boolean'],
            'renewal_reminder_date' => ['nullable', 'date'],
        ]);

        $certification = $request->user()->certifications()->where('slug', $certificationSlug)->firstOrFail();
        Gate::authorize('view', $certification);

        $certification->credentials()->create([
            'user_id' => $request->user()->id,
            'credential_name' => $data['credential_name'],
            'provider_name' => $data['provider_name'],
            'issue_date' => $data['issue_date'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'credential_id' => $data['credential_id'] ?? null,
            'verification_url' => $data['verification_url'] ?? null,
            'linkedin_added' => $request->boolean('linkedin_added'),
            'cv_added' => $request->boolean('cv_added'),
            'renewal_reminder_date' => $data['renewal_reminder_date'] ?? null,
        ]);

        return redirect()->route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'credentials'])->with('status', 'Credential recorded.');
    }
}
