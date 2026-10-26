@extends('layouts.app')

@section('title', $product->exists ? __('messages.edit_product') : __('messages.add_product'))

@section('content')
    <section class="form-card">
        <div class="page-heading"><h1>{{ $product->exists ? __('messages.edit_product') : __('messages.add_product') }}</h1></div>
        <form class="stack-form" action="{{ $product->exists ? route('seller.products.update', $product) : route('seller.products.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            @if ($product->exists) @method('put') @endif
            <label>{{ __('messages.product_name') }}<input name="name" value="{{ old('name', $product->name) }}" maxlength="160" placeholder="{{ __('messages.placeholder_product_name') }}" required></label>
            <label>{{ __('messages.product_type') }}
                <select name="product_type" data-product-type-select required>
                    @foreach (['material', 'equipment', 'machinery'] as $type)
                        <option value="{{ $type }}" @selected(old('product_type', $product->product_type ?? 'material') === $type)>{{ __("messages.$type") }}</option>
                    @endforeach
                </select>
            </label>
            <label>{{ __('messages.category') }}
                <select name="category_id" required>
                    <option value="">{{ __('messages.all_categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->display_name }}</option>
                    @endforeach
                </select>
            </label>
            <label>{{ __('messages.description') }}<textarea name="description" rows="6" maxlength="5000" placeholder="{{ __('messages.placeholder_description') }}" required>{{ old('description', $product->description) }}</textarea></label>
            <label>{{ __('messages.product_image') }}<input name="image" type="file" accept="image/*"></label>
            @if ($product->image_path)
                <img class="product-image" src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" alt="{{ $product->name }}">
            @endif
            <div class="form-columns">
                <label>{{ __('messages.price') }}<input name="price" type="number" min="0.01" step="0.01" value="{{ old('price', $product->price) }}" placeholder="{{ __('messages.placeholder_price') }}" required></label>
                <label>{{ __('messages.stock') }}<input name="stock" type="number" min="0" max="1000000" value="{{ old('stock', $product->stock ?? 0) }}" placeholder="{{ __('messages.placeholder_quantity') }}" required></label>
            </div>
            <fieldset class="stack-form" data-product-fields="material,machinery">
                <legend>{{ __('messages.material_details') }}</legend>
                <label>{{ __('messages.manufacturing_company') }}<input name="manufacturing_company" data-required-product-type="material,machinery" value="{{ old('manufacturing_company', $product->manufacturing_company) }}" maxlength="160" placeholder="{{ __('messages.placeholder_company_name') }}"></label>
                <label>{{ __('messages.manufacturing_country') }}<input name="manufacturing_country" data-required-product-type="material,machinery" value="{{ old('manufacturing_country', $product->manufacturing_country) }}" maxlength="120" placeholder="{{ __('messages.placeholder_country') }}"></label>
            </fieldset>
            <fieldset class="stack-form" data-product-fields="material,machinery">
                <legend>{{ __('messages.production_details') }}</legend>
                <div class="form-columns">
                    <label>{{ __('messages.production_date') }}<input name="production_date" type="date" data-required-product-type="material,machinery" value="{{ old('production_date', $product->production_date?->toDateString()) }}" max="{{ now()->toDateString() }}"></label>
                    <label>{{ __('messages.expiry_date') }}<input name="expiry_date" type="date" data-required-product-type="material,machinery" value="{{ old('expiry_date', $product->expiry_date?->toDateString()) }}" min="{{ $product->production_date?->toDateString() }}"></label>
                </div>
            </fieldset>
            <fieldset class="stack-form" data-product-fields="machinery">
                <legend>{{ __('messages.machinery_details') }}</legend>
                <label>{{ __('messages.driver_name') }}<input name="driver_name" data-required-product-type="machinery" value="{{ old('driver_name', $product->driver_name) }}" maxlength="120" placeholder="{{ __('messages.placeholder_name') }}"></label>
                <label>{{ __('messages.driver_phone') }}<input name="driver_phone" type="tel" data-required-product-type="machinery" value="{{ old('driver_phone', $product->driver_phone) }}" maxlength="24" placeholder="{{ __('messages.placeholder_phone') }}"></label>
            </fieldset>
            <fieldset class="stack-form" data-product-fields="equipment,machinery">
                <legend>{{ __('messages.rental_details') }}</legend>
                <label class="check-row">
                    <input name="is_rentable" type="checkbox" value="1" data-rentable-toggle @checked(old('is_rentable', $product->is_rentable))>
                    <span>{{ __('messages.available_for_rent') }}</span>
                </label>
                <label data-rental-rate-field>{{ __('messages.rental_rate_per_day') }}
                    <input name="rental_rate_per_day" type="number" min="0.01" step="0.01" value="{{ old('rental_rate_per_day', $product->rental_rate_per_day) }}" placeholder="{{ __('messages.placeholder_price') }}">
                </label>
            </fieldset>
            <fieldset class="stack-form">
                <legend>{{ __('messages.product_information') }}</legend>
                <label>{{ __('messages.storage_info') }}<textarea name="storage_info" rows="2" maxlength="3000" placeholder="{{ __('messages.placeholder_storage_info') }}">{{ old('storage_info', $product->storage_info) }}</textarea></label>
                <label>{{ __('messages.risk_info') }}<textarea name="risk_info" rows="2" maxlength="3000" placeholder="{{ __('messages.placeholder_risk_info') }}">{{ old('risk_info', $product->risk_info) }}</textarea></label>
                <label>{{ __('messages.usage_info') }}<textarea name="usage_info" rows="2" maxlength="3000" placeholder="{{ __('messages.placeholder_usage_info') }}">{{ old('usage_info', $product->usage_info) }}</textarea></label>
            </fieldset>
            <p class="muted">{{ __('messages.product_submitted') }}</p>
            <button class="button button-dark" type="submit">{{ __('messages.save') }}</button>
        </form>
    </section>
@endsection
