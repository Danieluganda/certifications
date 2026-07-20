<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreWeeklyAvailability extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'day_of_week' => ['required', 'integer', 'min:1', 'max:7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $request->user()->weeklyAvailabilities()->updateOrCreate(
            [
                'day_of_week' => $data['day_of_week'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
            ],
            ['is_active' => $request->boolean('is_active', true)]
        );

        return redirect()
            ->route('dashboard.page', ['dashboardPage' => 'planner'])
            ->with('status', 'Availability saved.');
    }
}
