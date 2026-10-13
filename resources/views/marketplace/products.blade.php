@extends('layouts.app')

@section('title', __('messages.products'))

@section('content')
    <div class="page-heading">
        <p class="eyebrow">{{ __('messages.brand') }}</p>
        <h1>{{ __('messages.browse_products') }}</h1>
    </div>
    <form class="search-form" method="get" action="{{ route('products.index') }}">
        <input name="q" value="{{ request('q') }}" placeholder="{{ __('messages.search') }}" aria-label="{{ __('messages.search') }}" maxlength="100">
        <select name="product_type" aria-label="{{ __('messages.product_type') }}">
            <option value="">{{ __('messages.all_product_types') }}</option>
            @foreach (['material', 'equipment', 'machinery'] as $type)
                <option value="{{ $type }}" @selected(request('product_type') === $type)>{{ __("messages.$type") }}</option>
            @endforeach
        </select>
        <select name="category" aria-label="{{ __('messages.category') }}">
            <option value="">{{ __('messages.all_categories') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->display_name }}</option>
            @endforeach
        </select>
        <input type="number" name="min_price" min="0" step="0.01" value="{{ request('min_price') }}" placeholder="{{ __('messages.min_price') }}" aria-label="{{ __('messages.min_price') }}">
        <input type="number" name="max_price" min="0" step="0.01" value="{{ request('max_price') }}" placeholder="{{ __('messages.max_price') }}" aria-label="{{ __('messages.max_price') }}">
        <select name="availability" aria-label="{{ __('messages.availability') }}">
            <option value="">{{ __('messages.all_availability') }}</option>
            <option value="in_stock" @selected(request('availability') === 'in_stock')>{{ __('messages.available') }}</option>
            <option value="out_of_stock" @selected(request('availability') === 'out_of_stock')>{{ __('messages.unavailable') }}</option>
        </select>
        <input type="number" name="min_stock" min="0" max="1000000" value="{{ request('min_stock') }}" placeholder="{{ __('messages.minimum_quantity') }}" aria-label="{{ __('messages.minimum_quantity') }}">
        <button class="button button-dark" type="submit">{{ __('messages.filter') }}</button>
    </form>
    <div class="card-grid">
        @forelse ($products as $product)
            <article class="panel listing-card">
                @if ($product->image_path)
                    <img class="product-image" src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" alt="{{ $product->name }}">
                @endif
                <p class="eyebrow">{{ $product->category?->display_name }}</p>
                <p class="eyebrow">{{ __("messages.{$product->product_type}") }}</p>
                <h2>{{ $product->name }}</h2>
                <p>{{ number_format((float) $product->price, 2) }} {{ __('messages.currency') }} · {{ $product->seller->business_name ?: $product->seller->name }}</p>
                @if ($product->is_rentable)
                    <p>{{ __('messages.rent') }}: {{ number_format((float) $product->rental_rate_per_day, 2) }} {{ __('messages.currency') }} / {{ __('messages.day') }}</p>
                @endif
                <p>{{ __('messages.stock') }}: {{ $product->stock <= 0 ? __('messages.unavailable') : $product->stock }}</p>
                <p class="muted">{{ \Illuminate\Support\Str::limit($product->description, 130) }}</p>
                <a class="button button-outline" href="{{ route('products.show', $product) }}">{{ __('messages.view_product') }}</a>
            </article>
        @empty
            <p class="empty-state">{{ __('messages.no_products') }}</p>
        @endforelse
    </div>
    {{ $products->links() }}
@endsection
