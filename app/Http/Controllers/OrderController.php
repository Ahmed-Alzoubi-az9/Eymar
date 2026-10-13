<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Notifications\MarketplaceNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $orders = Order::query()
            ->with(['buyer', 'seller', 'items'])
            ->where(fn ($query) => $query->where('buyer_id', $user->id)->orWhere('seller_id', $user->id))
            ->latest()
            ->paginate(15);

        return view('orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless(
            in_array($request->user()->id, [$order->buyer_id, $order->seller_id], true)
                || $request->user()->role === 'admin',
            404,
        );

        return view('orders.show', ['order' => $order->load(['buyer', 'seller', 'items', 'payments', 'review'])]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,shipped,delivered,cancelled'],
        ]);

        DB::transaction(function () use ($request, $order, $validated): void {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $user = $request->user();
            $nextStatus = $validated['status'];

            if ($user->role === 'admin') {
                $allowed = [
                    'pending' => ['confirmed', 'cancelled'],
                    'confirmed' => ['shipped', 'cancelled'],
                    'shipped' => ['delivered'],
                ];
            } elseif ($user->id === $lockedOrder->buyer_id) {
                $allowed = [
                    'pending' => ['cancelled'],
                    'shipped' => ['delivered'],
                ];
            } elseif ($user->id === $lockedOrder->seller_id) {
                $allowed = [
                    'pending' => ['confirmed', 'cancelled'],
                    'confirmed' => ['shipped', 'cancelled'],
                ];
            } else {
                abort(404);
            }

            if (! in_array($nextStatus, $allowed[$lockedOrder->status] ?? [], true)) {
                throw ValidationException::withMessages([
                    'status' => __('messages.invalid_order_transition'),
                ]);
            }

            if ($nextStatus === 'cancelled') {
                foreach ($lockedOrder->items as $item) {
                    if ($item->fulfillment_type === 'purchase') {
                        $item->product()->lockForUpdate()->first()?->increment('stock', $item->quantity);
                    }
                }
            }

            $lockedOrder->update(['status' => $nextStatus]);
            if ($nextStatus === 'delivered' && $lockedOrder->payment_method === 'cash_on_delivery') {
                $lockedOrder->update(['payment_status' => 'paid']);
            }
            $recipientId = $user->id === $lockedOrder->buyer_id
                ? $lockedOrder->seller_id
                : $lockedOrder->buyer_id;
            $recipient = User::query()->findOrFail($recipientId);
            $recipient->notify(new MarketplaceNotification(
                'Order status updated',
                "Order #{$lockedOrder->id} is now {$nextStatus}.",
                route('orders.show', $lockedOrder),
            ));
        });

        return back()->with('success', __('messages.order_status_updated'));
    }
}
