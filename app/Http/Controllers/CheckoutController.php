<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Notifications\MarketplaceNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $items = $request->user()->cartItems()->with('product')->get();
        if ($items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        return view('checkout.create', compact('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shipping_name' => ['required', 'string', 'max:120'],
            'shipping_phone' => ['required', 'string', 'max:32'],
            'shipping_address' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'in:cash_on_delivery,online'],
        ]);

        $orders = DB::transaction(function () use ($request, $validated): Collection {
            $cartItems = $request->user()->cartItems()->orderBy('id')->lockForUpdate()->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => __('messages.cart_empty'),
                ]);
            }

            $orders = collect();

            foreach ($cartItems->groupBy(fn ($item) => $item->product()->value('seller_id')) as $sellerId => $sellerItems) {
                $preparedItems = $sellerItems->map(function ($item) use ($request) {
                    $product = Product::query()->whereKey($item->product_id)->lockForUpdate()->first();

                    if (
                        ! $product
                        || ! $product->is_active
                        || $product->seller_id === $request->user()->id
                        || $product->seller->status !== 'active'
                        || ($item->fulfillment_type === 'purchase' && $item->quantity > $product->stock)
                        || ($item->fulfillment_type === 'rental' && (
                            ! $product->is_rentable
                            || ! in_array($product->product_type, ['equipment', 'machinery'], true)
                            || $this->reservedRentalQuantity($product->id, $item->rental_start_date, $item->rental_days) + $item->quantity > $product->stock
                        ))
                    ) {
                        throw ValidationException::withMessages([
                            'cart' => __('messages.cart_stock_changed'),
                        ]);
                    }

                    return [$item, $product];
                });

                $totalCents = $preparedItems->sum(function (array $row): int {
                    [$item, $product] = $row;
                    $unitPrice = $item->fulfillment_type === 'rental'
                        ? $product->rental_rate_per_day
                        : $product->price;
                    $days = $item->fulfillment_type === 'rental' ? $item->rental_days : 1;

                    return $this->toCents((string) $unitPrice) * $item->quantity * $days;
                });
                if ($totalCents > 9_999_999_999) {
                    throw ValidationException::withMessages([
                        'cart' => __('messages.order_total_too_high'),
                    ]);
                }

                $order = Order::query()->create([
                    'buyer_id' => $request->user()->id,
                    'seller_id' => (int) $sellerId,
                    'status' => 'pending',
                    'payment_method' => $validated['payment_method'] === 'online' ? 'online' : 'cash_on_delivery',
                    'payment_status' => 'pending',
                    'total' => sprintf('%d.%02d', intdiv($totalCents, 100), $totalCents % 100),
                    ...$validated,
                ]);

                // Cash orders get a pending payment row immediately; online orders
                // get their Payment row on the simulated gateway page (never storing card data).
                if ($validated['payment_method'] === 'cash_on_delivery') {
                    $order->payments()->create([
                        'method' => 'cash_on_delivery',
                        'amount' => $order->total,
                        'status' => 'pending',
                    ]);
                }

                foreach ($preparedItems as [$item, $product]) {
                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'unit_price' => $item->fulfillment_type === 'rental' ? $product->rental_rate_per_day : $product->price,
                        'quantity' => $item->quantity,
                        'fulfillment_type' => $item->fulfillment_type,
                        'rental_days' => $item->rental_days,
                        'rental_start_date' => $item->rental_start_date,
                        'return_date' => $item->fulfillment_type === 'rental'
                            ? $item->rental_start_date->copy()->addDays($item->rental_days)
                            : null,
                    ]);

                    if ($item->fulfillment_type === 'purchase') {
                        $product->decrement('stock', $item->quantity);
                    }
                }

                $order->seller->notify(new MarketplaceNotification(
                    'New marketplace order',
                    "Order #{$order->id} is waiting for your confirmation.",
                    route('orders.show', $order),
                ));
                $orders->push($order);
            }

            $request->user()->cartItems()->delete();

            return $orders;
        });

        if (($validated['payment_method'] ?? null) === 'online') {
            $request->session()->put('pending_online_orders', $orders->pluck('id')->all());

            return redirect()->route('payments.gateway')->with(
                'success',
                'Orders created. Complete the simulated online payment to notify sellers.',
            );
        }

        return redirect()->route('orders.index')->with(
            'success',
            __('messages.orders_placed', ['count' => $orders->count()]),
        );
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0', STR_PAD_RIGHT);
    }

    private function reservedRentalQuantity(int $productId, mixed $startDate, int $rentalDays): int
    {
        $rentalStart = $startDate->toDateString();
        $returnDate = $startDate->copy()->addDays($rentalDays)->toDateString();

        return OrderItem::query()
            ->where('product_id', $productId)
            ->where('fulfillment_type', 'rental')
            ->where('rental_start_date', '<', $returnDate)
            ->where('return_date', '>', $rentalStart)
            ->whereHas('order', fn ($orders) => $orders->whereNotIn('status', ['cancelled']))
            ->sum('quantity');
    }
}
