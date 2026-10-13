<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ProviderSchedule;
use App\Models\User;
use App\Notifications\MarketplaceNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        // Auto-expire pending bookings older than 24h.
        Booking::query()->where('status', 'pending')
            ->where('created_at', '<', now()->subDay())->update(['status' => 'expired']);

        $bookings = Booking::query()->with(['provider', 'customer', 'serviceRequest'])
            ->where(fn ($q) => $q->where('customer_id', $user->id)->orWhere('provider_id', $user->id))
            ->latest()->paginate(15);

        return view('bookings.index', compact('bookings'));
    }

    public function store(Request $request, User $provider): RedirectResponse
    {
        abort_unless($provider->serviceProviderProfile()->exists() && $provider->status === 'active', 404);
        abort_if($provider->id === $request->user()->id, 403);

        $validated = $request->validate([
            'service_request_id' => ['nullable', 'exists:service_requests,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['required', 'integer', 'min:30', 'max:480'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Must fall inside an AVAILABLE schedule slot.
        $endTime = \Carbon\Carbon::createFromFormat('H:i', $validated['start_time'])->addMinutes($validated['duration_minutes'])->format('H:i');
        $slot = ProviderSchedule::query()
            ->where('provider_id', $provider->id)
            ->where('date', $validated['date'])
            ->where('is_available', true)
            ->where('start_time', '<=', $validated['start_time'])
            ->where('end_time', '>=', $endTime)
            ->first();

        if (! $slot) {
            throw ValidationException::withMessages(['date' => 'Provider is not available at that time. Pick a free slot.']);
        }

        // No double-booking of the same period.
        $clash = Booking::query()->where('provider_id', $provider->id)
            ->where('date', $validated['date'])->whereIn('status', ['pending', 'accepted'])
            ->where('start_time', '<', $endTime)->get()
            ->first(function (Booking $b) use ($validated) {
                $bEnd = \Carbon\Carbon::createFromFormat('H:i:s', $b->start_time)->addMinutes($b->duration_minutes)->format('H:i');
                return $b->start_time <= $validated['start_time'] && $bEnd > $validated['start_time']
                    || $validated['start_time'] < $bEnd && $endTime > $b->start_time;
            });
        if ($clash) {
            throw ValidationException::withMessages(['start_time' => 'That hour was just booked. Try another time.']);
        }

        $booking = DB::transaction(function () use ($request, $provider, $validated) {
            $booking = Booking::create([
                ...$validated, 'customer_id' => $request->user()->id, 'provider_id' => $provider->id, 'status' => 'pending',
            ]);
            $provider->notify(new MarketplaceNotification(
                'New booking request',
                "{$request->user()->name} requested {$validated['date']} at {$validated['start_time']}.",
                route('bookings.index'),
            ));
            return $booking;
        });

        return redirect()->route('bookings.index')->with('success', "Booking #{$booking->id} sent. Provider has 24h to respond.");
    }

    public function respond(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->provider_id === $request->user()->id, 403);
        $status = $request->validate(['status' => ['required', 'in:accepted,rejected']])['status'];
        abort_unless($booking->status === 'pending', 422);

        $booking->update(['status' => $status]);
        $booking->customer->notify(new MarketplaceNotification(
            "Booking {$status}",
            "Your booking #{$booking->id} for {$booking->date->toDateString()} was {$status}.",
            route('bookings.index'),
        ));

        return back()->with('success', "Booking {$status}.");
    }
}
