<?php

namespace App\Http\Controllers\Certifications;

use App\Domains\Certifications\Actions\ActivateFreeCredential;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ActivateFreeCredentialController extends Controller
{
    public function __invoke(Request $request, string $certificationSlug, ActivateFreeCredential $activateFreeCredential): RedirectResponse
    {
        $certification = $request->user()
            ->certifications()
            ->where('slug', $certificationSlug)
            ->firstOrFail();

        try {
            $activateFreeCredential->execute($request->user(), $certification);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['certification' => $exception->getMessage()]);
        }

        return back()->with('status', 'Free credential activated.');
    }
}
