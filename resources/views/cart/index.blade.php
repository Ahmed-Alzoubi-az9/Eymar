@extends('layouts.app')

@section('title', __('messages.cart'))

@section('content')
    <div class="page-heading"><p class="eyebrow">{{ __('messages.products') }}</p><h1>{{ __('messages.cart') }}</h1></div>
    @if ($items->isEmpty())
        <div class="panel empty-state">
            <p>{{ __('messages.empty_cart') }}</p>
            <a class="button button-dark" href="{{ route('products.index') }}">{{ __('messages.continue_shopping') }}</a>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead><tr><th>{{ __('messages.product_name') }}</th><th>{{ __('messages.seller') }}</th><th>{{ __('messages.order_type') }}</th><th>{{ __('messages.price') }}</th><th>{{ __('messages.quantity') }}</th><th>{{ __('messages.total') }}</th><th>{{ __('messages.actions') }}</th></tr></thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->product->seller->business_name ?: $item->product->seller->name }}</td>
                            <td>
                                {{ __("messages.{$item->fulfillment_type}") }}
                                @if ($item->fulfillment_type === 'rental')
                                    @php($rentalEnd = $item->rental_start_date->copy()->addDays($item->rental_days))
                                    <br>{{ $item->rental_start_date?->toDateString() }} – {{ $rentalEnd->toDateString() }}
                                    <br>{{ $item->rental_days }} {{ __('messages.days') }}
                                @endif
                            </td>
                            <td>
                                {{ number_format((float) ($item->fulfillment_type === 'rental' ? $item->product->rental_rate_per_day : $item->product->price), 2) }} {{ __('messages.currency') }}
                                @if ($item->fulfillment_type === 'rental') / {{ __('messages.day') }} @endif
                            </td>
                            <td>
                                <form class="inline-form" action="{{ route('cart.update', $item) }}" method="post">
                                    @csrf @method('put')
                                    <input type="hidden" name="fulfillment_type" value="{{ $item->fulfillment_type }}">
                                    <input class="quantity-input" type="number" name="quantity" min="1" max="{{ $item->product->stock }}" value="{{ $item->quantity }}" placeholder="{{ __('messages.placeholder_quantity') }}" required>
                                    @if ($item->fulfillment_type === 'rental')
                                        <input type="number" name="rental_days" min="1" max="365" value="{{ $item->rental_days }}" placeholder="{{ __('messages.placeholder_quantity') }}" required>
                                        <input type="date" name="rental_start_date" min="{{ now()->toDateString() }}" value="{{ $item->rental_start_date?->toDateString() }}" required>
                                    @endif
                                    <button class="text-button" type="submit">{{ __('messages.update_cart') }}</button>
                                </form>
                            </td>
                            <td>{{ number_format((float) ($item->fulfillment_type === 'rental' ? $item->product->rental_rate_per_day : $item->product->price) * $item->quantity * ($item->fulfillment_type === 'rental' ? $item->rental_days : 1), 2) }} {{ __('messages.currency') }}</td>
                            <td>
                                <form action="{{ route('cart.destroy', $item) }}" method="post">
                                    @csrf @method('delete')
                                    <button class="text-button" type="submit">{{ __('messages.remove') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="page-heading page-heading-row cart-footer">
            <a class="button button-outline" href="{{ route('products.index') }}">{{ __('messages.continue_shopping') }}</a>
            <a class="button button-dark" href="{{ route('checkout.create') }}">{{ __('messages.checkout') }}</a>
        </div>
    @endif
@endsection
