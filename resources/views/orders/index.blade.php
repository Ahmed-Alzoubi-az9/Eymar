@extends('layouts.app')

@section('title', __('messages.orders'))

@section('content')
    <div class="page-heading"><p class="eyebrow">{{ __('messages.dashboard') }}</p><h1>{{ __('messages.orders') }}</h1></div>
    <div class="card-grid">
        @forelse ($orders as $order)
            <article class="panel listing-card">
                <p class="eyebrow">{{ __('messages.order_number', ['id' => $order->id]) }}</p>
                <h2>{{ __('messages.'.strtolower($order->status)) }}</h2>
                <p>{{ __('messages.seller') }}: {{ $order->seller->business_name ?: $order->seller->name }}</p>
                <p>{{ __('messages.total') }}: {{ number_format((float) $order->total, 2) }} {{ __('messages.currency') }}</p>
                <p>{{ __('messages.payment_status') }}: {{ __('messages.'.$order->payment_status) }}</p>
                <a class="button button-outline" href="{{ route('orders.show', $order) }}">{{ __('messages.order_status') }}</a>
            </article>
        @empty
            <p class="empty-state">{{ __('messages.no_orders') }}</p>
        @endforelse
    </div>
    {{ $orders->links() }}
@endsection
