<?php

namespace App\Http\Controllers\Dashboard;

use App\Domains\Certifications\Models\Certification;
use App\Domains\Planning\Models\PlannerRecommendation;
use App\Domains\Planning\Models\StudyGoal;
use App\Domains\Planning\Models\StudySession;
use App\Domains\Specialisations\Models\AnalyticsProperty;
use App\Domains\Specialisations\Models\Dataset;
use App\Domains\Specialisations\Models\OntologyResource;
use App\Domains\Specialisations\Models\SearchIndex;
use App\Domains\Specialisations\Models\Specialisation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowDashboard extends Controller
{
    public function __invoke(Request $request, string $dashboardPage = 'learn'): View
    {
        abort_unless(in_array($dashboardPage, ['learn', 'today', 'catalogue', 'planner', 'roadmap', 'workspace', 'projects', 'resources', 'specialisations'], true), 404);

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

        $today = now()->startOfDay();
        $endOfWeek = now()->endOfWeek();
        $studySessions = StudySession::query()
            ->with(['certification', 'lesson', 'topic', 'tasks'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['Pending', 'in_progress'])
            ->orderByRaw('COALESCE(scheduled_start, scheduled_for)')
            ->take(12)
            ->get();

        return view('dashboard.index', [
            'user' => $user,
            'certifications' => $certifications,
            'primary' => $primary,
            'activeFreeCredentials' => $activeFreeCredentials,
            'projects' => $certifications->flatMap->projects,
            'resources' => $certifications->flatMap->resources,
            'dashboardPage' => $dashboardPage,
            'studySessions' => $studySessions,
            'todaySessions' => $studySessions->filter(fn (StudySession $session): bool => ($session->scheduled_start ?? $session->scheduled_for)?->isSameDay($today)),
            'weekSessions' => $studySessions->filter(fn (StudySession $session): bool => ($session->scheduled_start ?? $session->scheduled_for)?->betweenIncluded($today, $endOfWeek)),
            'studyGoals' => StudyGoal::query()
                ->with('certification')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->orderBy('ends_on')
                ->take(8)
                ->get(),
            'studyStreak' => $user->studyStreak,
            'plannerRecommendations' => PlannerRecommendation::query()
                ->with('certification')
                ->where('user_id', $user->id)
                ->whereNull('accepted_at')
                ->whereNull('dismissed_at')
                ->orderBy('priority')
                ->orderBy('recommended_date')
                ->take(6)
                ->get(),
            'projectMilestones' => $user->projectMilestones()
                ->with('project.certification')
                ->whereIn('status', ['Planned', 'in_progress'])
                ->orderByRaw('target_date IS NULL, target_date')
                ->take(6)
                ->get(),
            'specialisations' => Specialisation::query()
                ->with('certifications.provider')
                ->where('user_id', $user->id)
                ->orderBy('priority')
                ->orderBy('name')
                ->get(),
            'datasets' => Dataset::query()
                ->with(['certification', 'specialisation'])
                ->where('user_id', $user->id)
                ->orderBy('dataset_type')
                ->get(),
            'ontologyResources' => OntologyResource::query()
                ->with('specialisation')
                ->where('user_id', $user->id)
                ->orderBy('resource_type')
                ->get(),
            'searchIndexes' => SearchIndex::query()
                ->with('project.certification')
                ->where('user_id', $user->id)
                ->orderBy('engine')
                ->get(),
            'analyticsProperties' => AnalyticsProperty::query()
                ->with('project.certification')
                ->where('user_id', $user->id)
                ->orderBy('provider')
                ->get(),
        ]);
    }
}
