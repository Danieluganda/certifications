<?php

namespace App\Http\Controllers\Certifications;

use App\Domains\Certifications\Actions\SetPrimaryCertification;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class SetPrimaryCertificationController extends Controller
{
    public function __invoke(Request $request, string $certificationSlug, SetPrimaryCertification $setPrimaryCertification): RedirectResponse
    {
        $certification = $request->user()
            ->certifications()
            ->where('slug', $certificationSlug)
            ->firstOrFail();

        try {
            $setPrimaryCertification->execute($request->user(), $certification);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['certification' => $exception->getMessage()]);
        }

        return back()->with('status', 'Primary paid certification updated.');
    }
}
