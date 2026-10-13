@extends('layouts.app')

@section('title', __('messages.manage_orders'))

@section('content')
    <div class="page-heading"><p class="eyebrow">{{ __('messages.admin') }}</p><h1>{{ __('messages.manage_orders') }}</h1></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>{{ __('messages.order_number', ['id' => '#']) }}</th><th>{{ __('messages.customer_label') }}</th><th>{{ __('messages.seller') }}</th><th>{{ __('messages.total') }}</th><th>{{ __('messages.order_status') }}</th><th>{{ __('messages.actions') }}</th></tr></thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td><a href="{{ route('orders.show', $order) }}">#{{ $order->id }}</a></td>
                        <td>{{ $order->buyer->name }}</td><td>{{ $order->seller->business_name ?: $order->seller->name }}</td>
                        <td>{{ number_format((float) $order->total, 2) }} {{ __('messages.currency') }}</td><td>{{ __("messages.{$order->status}") }}</td>
                        <td><a href="{{ route('orders.show', $order) }}">{{ __('messages.order_status') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6">{{ __('messages.no_orders') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $orders->links() }}
@endsection
