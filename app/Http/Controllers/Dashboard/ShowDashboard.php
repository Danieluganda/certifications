<?php

namespace App\Http\Controllers\Dashboard;

use App\Domains\Certifications\Models\Certification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowDashboard extends Controller
{
    public function __invoke(Request $request, string $dashboardPage = 'learn'): View
    {
        abort_unless(in_array($dashboardPage, ['learn', 'today', 'catalogue', 'planner', 'roadmap', 'workspace', 'projects', 'resources'], true), 404);

        $user = $request->user()->load('profile');

        $certifications = Certification::query()
            ->with(['provider', 'domains', 'lessons', 'projects', 'resources'])
            ->where('user_id', $user->id)
            ->orderByDesc('is_primary')
            ->orderBy('track_type')
            ->orderByDesc('priority')
            ->get();

        $primary = $certifications->firstWhere('is_primary', true) ?? $certifications->first();
        $activeFreeCredentials = $certifications
            ->where('track_type.value', 'free_credential')
            ->where('status', 'Active');

        return view('dashboard.index', [
            'user' => $user,
            'certifications' => $certifications,
            'primary' => $primary,
            'activeFreeCredentials' => $activeFreeCredentials,
            'projects' => $certifications->flatMap->projects,
            'resources' => $certifications->flatMap->resources,
            'dashboardPage' => $dashboardPage,
            'studySessions' => $user->studySessions()
                ->with(['certification', 'lesson'])
                ->whereIn('status', ['Pending', 'in_progress'])
                ->orderBy('scheduled_for')
                ->take(6)
                ->get(),
        ]);
    }
}
