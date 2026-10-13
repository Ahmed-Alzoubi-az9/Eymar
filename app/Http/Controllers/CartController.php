<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $items = $request->user()->cartItems()->with(['product.seller'])->get();

        return view('cart.index', compact('items'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'fulfillment_type' => ['required', Rule::in(['purchase', 'rental'])],
            'rental_days' => ['required_if:fulfillment_type,rental', 'nullable', 'integer', 'min:1', 'max:365'],
            'rental_start_date' => ['required_if:fulfillment_type,rental', 'nullable', 'date', 'after_or_equal:today'],
        ]);

        abort_unless(
            $product->is_active
                && $product->seller_id !== $request->user()->id
                && $product->seller->status === 'active',
            404,
        );

        if (
            $validated['fulfillment_type'] === 'rental'
            && (! $product->is_rentable || ! in_array($product->product_type, ['equipment', 'machinery'], true))
        ) {
            throw ValidationException::withMessages([
                'fulfillment_type' => __('messages.product_not_rentable'),
            ]);
        }

        DB::transaction(function () use ($request, $product, $validated): void {
            $lockedProduct = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $item = CartItem::query()->firstOrNew([
                'user_id' => $request->user()->id,
                'product_id' => $lockedProduct->id,
            ]);
            $quantity = ($item->exists ? $item->quantity : 0) + $validated['quantity'];

            if (
                ! $lockedProduct->is_active
                || $quantity > $lockedProduct->stock
                || ($item->exists && $item->fulfillment_type !== $validated['fulfillment_type'])
                || ($item->exists && $item->fulfillment_type === 'rental' && (
                    $item->rental_days !== $validated['rental_days']
                    || $item->rental_start_date?->toDateString() !== $validated['rental_start_date']
                ))
            ) {
                throw ValidationException::withMessages([
                    'quantity' => __('messages.cart_conflicting_item'),
                ]);
            }

            $item->quantity = $quantity;
            $item->fulfillment_type = $validated['fulfillment_type'];
            $item->rental_days = $validated['fulfillment_type'] === 'rental' ? $validated['rental_days'] : null;
            $item->rental_start_date = $validated['fulfillment_type'] === 'rental' ? $validated['rental_start_date'] : null;
            $item->save();
        });

        return redirect()->route('cart.index')->with('success', __('messages.added_to_cart'));
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->user_id === $request->user()->id, 404);
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'fulfillment_type' => ['required', Rule::in(['purchase', 'rental'])],
            'rental_days' => ['required_if:fulfillment_type,rental', 'nullable', 'integer', 'min:1', 'max:365'],
            'rental_start_date' => ['required_if:fulfillment_type,rental', 'nullable', 'date', 'after_or_equal:today'],
        ]);

        $product = $cartItem->product;
        abort_unless($product && $product->is_active, 404);
        if ($validated['fulfillment_type'] !== $cartItem->fulfillment_type) {
            throw ValidationException::withMessages([
                'fulfillment_type' => __('messages.cart_conflicting_item'),
            ]);
        }

        if (
            $validated['quantity'] > $product->stock
            || ($cartItem->fulfillment_type === 'rental' && ! $product->is_rentable)
        ) {
            throw ValidationException::withMessages([
                'quantity' => __('messages.insufficient_stock'),
            ]);
        }

        $cartItem->update([
            'quantity' => $validated['quantity'],
            'rental_days' => $cartItem->fulfillment_type === 'rental' ? $validated['rental_days'] : null,
            'rental_start_date' => $cartItem->fulfillment_type === 'rental' ? $validated['rental_start_date'] : null,
        ]);

        return back()->with('success', __('messages.cart_updated'));
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->user_id === $request->user()->id, 404);
        $cartItem->delete();

        return back()->with('success', __('messages.cart_item_removed'));
    }
}
