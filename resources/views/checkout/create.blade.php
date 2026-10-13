@extends('layouts.app')

@section('title', __('messages.checkout'))

@section('content')
    <div class="page-heading"><p class="eyebrow">{{ __('messages.cart') }}</p><h1>{{ __('messages.checkout') }}</h1></div>
    <div class="checkout-grid">
        <section class="form-card">
            <form class="stack-form" action="{{ route('checkout.store') }}" method="post">
                @csrf
                <label>{{ __('messages.shipping_name') }}<input name="shipping_name" value="{{ old('shipping_name', auth()->user()->name) }}" maxlength="120" required></label>
                <label>{{ __('messages.shipping_phone') }}<input name="shipping_phone" type="tel" value="{{ old('shipping_phone', auth()->user()->phone) }}" maxlength="32" required></label>
                <label>{{ __('messages.shipping_address') }}<textarea name="shipping_address" rows="4" maxlength="2000" required>{{ old('shipping_address', auth()->user()->address ?: auth()->user()->location) }}</textarea></label>
                <label>{{ __('messages.notes') }}<textarea name="notes" rows="3" maxlength="1000">{{ old('notes') }}</textarea></label>
                <label>{{ __('messages.payment_method') }}
                    <select name="payment_method" required>
                        <option value="cash_on_delivery">{{ __('messages.cash_on_delivery') }}</option>
                        <option value="online">Online payment (demo gateway)</option>
                    </select>
                </label>
                <div class="notice">{{ __('messages.cash_on_delivery_notice') }} Online demo: use 4242 4242 4242 4242 to approve; card data is never stored.</div>
                <button class="button button-dark" type="submit">{{ __('messages.place_order') }}</button>
            </form>
        </section>
        <aside class="panel order-summary">
            <h2>{{ __('messages.cart') }}</h2>
            @foreach ($items as $item)
                @php
                    $unitPrice = $item->fulfillment_type === 'rental' ? $item->product->rental_rate_per_day : $item->product->price;
                    $itemTotal = (float) $unitPrice * $item->quantity * ($item->fulfillment_type === 'rental' ? $item->rental_days : 1);
                @endphp
                <p class="summary-line">
                    <span>{{ $item->product->name }} × {{ $item->quantity }} · {{ __("messages.{$item->fulfillment_type}") }}
                        @if ($item->fulfillment_type === 'rental')
                            @php($rentalEnd = $item->rental_start_date->copy()->addDays($item->rental_days))
                            <br>{{ $item->rental_days }} {{ __('messages.days') }} ({{ $item->rental_start_date?->toDateString() }} – {{ $rentalEnd->toDateString() }})
                        @endif
                    </span>
                    <strong>{{ number_format($itemTotal, 2) }} {{ __('messages.currency') }}</strong>
                </p>
            @endforeach
            <p class="muted">{{ __('messages.orders_placed', ['count' => $items->pluck('product.seller_id')->unique()->count()]) }}</p>
        </aside>
    </div>
@endsection
