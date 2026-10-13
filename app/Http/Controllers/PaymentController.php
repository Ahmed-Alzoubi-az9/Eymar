<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Notifications\MarketplaceNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentController extends Controller
{
    // Simulated gateway page. Card data is validated in format only and NEVER stored.
    public function gateway(Request $request): View|RedirectResponse
    {
        $orderIds = $request->session()->get('pending_online_orders', []);
        $orders = Order::query()->whereKey($orderIds)->where('buyer_id', $request->user()->id)
            ->where('payment_method', 'online')->where('payment_status', 'pending')->with('seller')->get();

        if ($orders->isEmpty()) {
            return redirect()->route('orders.index');
        }

        return view('payments.gateway', ['orders' => $orders, 'total' => $orders->sum('total')]);
    }

    public function process(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'card_number' => ['required', 'string', 'regex:/^[0-9 ]{12,23}$/'],
            'expiry' => ['required', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
            'cvc' => ['required', 'string', 'regex:/^[0-9]{3,4}$/'],
            'card_name' => ['required', 'string', 'max:120'],
        ]);

        $digits = preg_replace('/\D/', '', $validated['card_number']);
        $orderIds = $request->session()->get('pending_online_orders', []);
        $orders = Order::query()->whereKey($orderIds)->where('buyer_id', $request->user()->id)
            ->where('payment_method', 'online')->where('payment_status', 'pending')->lockForUpdate()->get();

        if ($orders->isEmpty()) {
            return redirect()->route('orders.index');
        }

        // Demo rule: the classic Stripe test decline card 4000 0000 0000 0002 always declines.
        $declined = $digits === '4000000000000002';

        DB::transaction(function () use ($orders, $digits, $declined): void {
            foreach ($orders as $order) {
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'method' => 'online',
                    'amount' => $order->total,
                    'status' => $declined ? 'failed' : 'paid',
                    'gateway_token' => 'EYM-'.strtoupper(Str::random(10)),
                    'card_last4' => substr($digits, -4),
                    'paid_at' => $declined ? null : now(),
                ]);
                if (! $declined) {
                    $order->update(['payment_status' => 'paid']);
                    $order->seller->notify(new MarketplaceNotification(
                        'Online payment received',
                        "Order #{$order->id} was paid online ({$payment->gateway_token}). Please confirm it.",
                        route('orders.show', $order),
                    ));
                }
            }
        });

        if ($declined) {
            return back()->withErrors(['card_number' => 'Card declined (demo). Try 4242 4242 4242 4242 or pay on delivery.'])->withInput();
        }

        $request->session()->forget('pending_online_orders');

        return redirect()->route('orders.index')->with('success', "Online payment approved. Receipt saved (card ending ".substr($digits, -4).").");
    }

    public function refund(Request $request, Order $order): RedirectResponse
    {
        abort_unless($request->user()->id === $order->seller_id || $request->user()->role === 'admin', 403);
        $payment = Payment::query()->where('order_id', $order->id)->where('status', 'paid')->latest()->first();
        abort_unless($payment && $payment->method === 'online', 422);

        $payment->update(['status' => 'refunded']);
        $order->update(['payment_status' => 'refunded']);
        $order->buyer->notify(new MarketplaceNotification(
            'Payment refunded',
            "Order #{$order->id} was refunded to your card.",
            route('orders.show', $order),
        ));

        return back()->with('success', 'Online payment refunded.');
    }
}
