<?php

namespace App\Http\Controllers;

use App\Models\ProviderSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $schedules = ProviderSchedule::query()
            ->where('provider_id', $request->user()->id)
            ->orderBy('date')->orderBy('start_time')
            ->paginate(20);

        return view('schedules.index', compact('schedules'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isServiceProvider(), 403);

        $validated = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_available' => ['sometimes', 'boolean'],
        ]);
        $validated['is_available'] = $request->boolean('is_available', true);

        // No-overlap check for the same provider/day.
        $overlap = ProviderSchedule::query()
            ->where('provider_id', $request->user()->id)
            ->where('date', $validated['date'])
            ->where('start_time', '<', $validated['end_time'])
            ->where('end_time', '>', $validated['start_time'])
            ->exists();

        if ($overlap) {
            return back()->withErrors(['start_time' => 'This slot overlaps an existing one.'])->withInput();
        }

        $request->user()->providerSchedules()->create($validated);

        return back()->with('success', 'Availability slot saved.');
    }

    public function destroy(Request $request, ProviderSchedule $schedule): RedirectResponse
    {
        abort_unless($schedule->provider_id === $request->user()->id, 403);
        $schedule->delete();

        return back()->with('success', 'Slot removed.');
    }
}
