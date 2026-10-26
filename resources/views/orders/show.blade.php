@extends('layouts.app')

@section('title', __('messages.order_number', ['id' => $order->id]))

@section('content')
    <section class="panel order-summary">
        <p class="eyebrow">{{ __('messages.order_number', ['id' => $order->id]) }}</p>
        <h1>{{ __('messages.'.strtolower($order->status)) }}</h1>
        <p>{{ __('messages.customer_label') }}: {{ $order->buyer->name }}</p>
        <p>{{ __('messages.seller') }}: {{ $order->seller->business_name ?: $order->seller->name }}</p>
        <p>{{ __('messages.shipping_name') }}: {{ $order->shipping_name }}</p>
        <p>{{ __('messages.shipping_phone') }}: {{ $order->shipping_phone }}</p>
        <p>{{ __('messages.shipping_address') }}: {{ $order->shipping_address }}</p>
        <p>{{ __('messages.payment_method') }}: {{ $order->payment_method === 'online' ? 'Online (demo gateway)' : __('messages.'.$order->payment_method) }}</p>
        <p>{{ __('messages.payment_status') }}: {{ __('messages.'.$order->payment_status) }}</p>
        @if ($order->notes)<p>{{ __('messages.notes') }}: {{ $order->notes }}</p>@endif
        @if ($order->payments->isNotEmpty())
            <div class="muted"><strong>Payments:</strong>
            @foreach ($order->payments as $payment)
                <br>#{{ $payment->id }} · {{ $payment->method }} · {{ $payment->status }} · {{ number_format((float) $payment->amount, 2) }} JOD
                @if ($payment->gateway_token) · token {{ $payment->gateway_token }} @endif
                @if ($payment->card_last4) · card …{{ $payment->card_last4 }} @endif
            @endforeach
            </div>
        @endif
        @if (in_array(auth()->id(), [$order->seller_id]) || auth()->user()->role === 'admin')
            @if ($order->payments->where('status', 'paid')->where('method', 'online')->isNotEmpty())
                <form class="status-form" action="{{ route('orders.refund', $order) }}" method="post">@csrf<button class="button button-outline" type="submit">Refund online payment</button></form>
            @endif
        @endif
    </section>
    <div class="table-wrap">
        <table>
            <thead><tr><th>{{ __('messages.product_name') }}</th><th>{{ __('messages.order_type') }}</th><th>{{ __('messages.price') }}</th><th>{{ __('messages.quantity') }}</th><th>{{ __('messages.total') }}</th></tr></thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}
                            @if ($item->fulfillment_type === 'rental')
                                <br>{{ __('messages.rental_period') }}: {{ $item->rental_start_date?->toDateString() }} – {{ $item->return_date?->toDateString() }} ({{ $item->rental_days }} {{ __('messages.days') }})
                            @endif
                        </td>
                        <td>{{ __("messages.{$item->fulfillment_type}") }}</td>
                        <td>{{ number_format((float) $item->unit_price, 2) }} {{ __('messages.currency') }} @if ($item->fulfillment_type === 'rental') / {{ __('messages.day') }} @endif</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format((float) $item->unit_price * $item->quantity * ($item->fulfillment_type === 'rental' ? $item->rental_days : 1), 2) }} {{ __('messages.currency') }}</td>
                    </tr>
                @endforeach
                <tr><th colspan="4">{{ __('messages.total') }}</th><th>{{ number_format((float) $order->total, 2) }} {{ __('messages.currency') }}</th></tr>
            </tbody>
        </table>
    </div>
    @php
        $nextStatus = match (true) {
            $order->status === 'pending' && auth()->id() === $order->buyer_id => 'cancelled',
            $order->status === 'pending' && auth()->id() === $order->seller_id => 'confirmed',
            $order->status === 'pending' && auth()->user()->role === 'admin' => 'confirmed',
            $order->status === 'confirmed' && auth()->id() === $order->seller_id => 'shipped',
            $order->status === 'confirmed' && auth()->user()->role === 'admin' => 'shipped',
            $order->status === 'shipped' && auth()->id() === $order->buyer_id => 'delivered',
            $order->status === 'shipped' && auth()->user()->role === 'admin' => 'delivered',
            default => null,
        };
        $nextStatusLabel = match ($nextStatus) {
            'cancelled' => 'cancel_order',
            'confirmed' => 'confirm_order',
            'shipped' => 'ship_order',
            'delivered' => 'deliver_order',
            default => null,
        };
    @endphp
    @if ($nextStatus && (auth()->id() === $order->buyer_id || auth()->id() === $order->seller_id || auth()->user()->role === 'admin'))
        <form class="status-form" action="{{ route('orders.status', $order) }}" method="post">
            @csrf @method('patch')
            <input type="hidden" name="status" value="{{ $nextStatus }}">
            <button class="button {{ $nextStatus === 'cancelled' ? 'button-outline' : 'button-dark' }}" type="submit">{{ __("messages.$nextStatusLabel") }}</button>
        </form>
    @endif

    <section class="section-block">
        <h2>Review seller</h2>
        @if ($order->review)
            <p><strong>{{ $order->review->rating }}/5</strong> — {{ $order->review->comment }}</p>
        @elseif ($order->buyer_id === auth()->id() && $order->status === 'delivered')
            <form class="stack-form form-card" action="{{ route('orders.reviews.store', $order) }}" method="post">
                @csrf
                <label>Rating (1–5)<input type="number" name="rating" min="1" max="5" placeholder="{{ __('messages.placeholder_review_rating') }}" required></label>
                <label>Comment<textarea name="comment" rows="3" maxlength="2000" placeholder="{{ __('messages.placeholder_comment') }}"></textarea></label>
                <button class="button button-dark" type="submit">Submit review</button>
            </form>
        @else
            <p class="muted">Reviews open after delivery.</p>
        @endif
    </section>
@endsection
