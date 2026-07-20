<?php

namespace App\Http\Controllers\Curriculum;

use App\Domains\Curriculum\Models\Lab;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompleteLab extends Controller
{
    public function __invoke(Request $request, Lab $lab): RedirectResponse
    {
        abort_unless($lab->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'reflection' => ['nullable', 'string', 'max:5000'],
        ]);

        $lab->update([
            'status' => 'completed',
            'completed_at' => now(),
            'reflection' => $data['reflection'] ?? $lab->reflection,
        ]);

        return redirect()
            ->route('certifications.show', ['certificationSlug' => $lab->certification->slug, 'workspacePage' => 'labs'])
            ->with('status', 'Lab marked complete.');
    }
}
