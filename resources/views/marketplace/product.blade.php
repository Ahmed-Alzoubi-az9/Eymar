@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <article class="panel product-detail">
        @if ($product->image_path)
            <img class="product-detail-image" src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" alt="{{ $product->name }}">
        @endif
        <div>
            <p class="eyebrow">{{ $product->category?->display_name }}</p>
            <p class="eyebrow">{{ __("messages.{$product->product_type}") }}</p>
            <h1>{{ $product->name }}</h1>
            <p>{{ __('messages.seller') }}: <a href="{{ route('professionals.show', $product->seller) }}">{{ $product->seller->business_name ?: $product->seller->name }}</a></p>
            <p class="price-large">{{ number_format((float) $product->price, 2) }} {{ __('messages.currency') }}</p>
            <p>{{ $product->description }}</p>
            <p>{{ __('messages.stock') }}: {{ $product->stock <= 0 ? __('messages.unavailable') : $product->stock }}</p>
            @if ($product->manufacturing_company || $product->manufacturing_country)
                <p>{{ __('messages.manufacturing_company') }}: {{ $product->manufacturing_company }} · {{ $product->manufacturing_country }}</p>
            @endif
            @if ($product->production_date || $product->expiry_date)
                <p>{{ __('messages.production_date') }}: {{ $product->production_date?->toDateString() }} · {{ __('messages.expiry_date') }}: {{ $product->expiry_date?->toDateString() }}</p>
            @endif
            @if ($product->driver_name || $product->driver_phone)
                <p>{{ __('messages.driver_name') }}: {{ $product->driver_name }} · {{ __('messages.driver_phone') }}: {{ $product->driver_phone }}</p>
            @endif
            @if ($product->storage_info)<p>{{ __('messages.storage_info') }}: {{ $product->storage_info }}</p>@endif
            @if ($product->risk_info)<p>{{ __('messages.risk_info') }}: {{ $product->risk_info }}</p>@endif
            @if ($product->usage_info)<p>{{ __('messages.usage_info') }}: {{ $product->usage_info }}</p>@endif
            @auth
                @if (auth()->user()->id !== $product->seller_id && $product->stock > 0)
                    <form class="stack-form" action="{{ route('cart.store', $product) }}" method="post">
                        @csrf
                        <label>{{ __('messages.order_type') }}
                            <select name="fulfillment_type" data-fulfillment-select>
                                <option value="purchase">{{ __('messages.purchase') }} — {{ number_format((float) $product->price, 2) }} {{ __('messages.currency') }}</option>
                                @if ($product->is_rentable)
                                    <option value="rental">{{ __('messages.rent') }} — {{ number_format((float) $product->rental_rate_per_day, 2) }} {{ __('messages.currency') }} / {{ __('messages.day') }}</option>
                                @endif
                            </select>
                        </label>
                        <label>{{ __('messages.quantity') }}
                            <input type="number" name="quantity" min="1" max="{{ $product->stock }}" value="1" placeholder="{{ __('messages.placeholder_quantity') }}" required>
                        </label>
                        @if ($product->is_rentable)
                            <div class="form-columns" data-rental-dates hidden>
                                <label>{{ __('messages.rental_days') }}
                                    <input type="number" name="rental_days" min="1" max="365" value="1" placeholder="{{ __('messages.placeholder_quantity') }}" data-rental-required>
                                </label>
                                <label>{{ __('messages.rental_start_date') }}
                                    <input type="date" name="rental_start_date" min="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}" data-rental-required>
                                </label>
                            </div>
                        @endif
                        <button class="button button-dark" type="submit">{{ __('messages.add_to_cart') }}</button>
                    </form>
                @endif
            @else
                <a class="button button-dark" href="{{ route('login') }}">{{ __('messages.login') }}</a>
            @endauth
        </div>
    </article>
@endsection
