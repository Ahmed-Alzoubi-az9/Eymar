@extends('layouts.app')

@section('title', __('messages.my_products'))

@section('content')
    <div class="page-heading page-heading-row">
        <div><p class="eyebrow">{{ __('messages.dashboard') }}</p><h1>{{ __('messages.my_products') }}</h1></div>
        <a class="button button-dark" href="{{ route('seller.products.create') }}">{{ __('messages.add_product') }}</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>{{ __('messages.product_name') }}</th><th>{{ __('messages.category') }}</th><th>{{ __('messages.price') }}</th><th>{{ __('messages.stock') }}</th><th>{{ __('messages.active') }}</th><th>{{ __('messages.actions') }}</th></tr></thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td><td>{{ __("messages.{$product->product_type}") }} · {{ $product->category?->display_name }}</td>
                        <td>{{ number_format((float) $product->price, 2) }} {{ __('messages.currency') }}</td><td>{{ $product->stock }}</td>
                        <td>{{ $product->is_active ? __('messages.active') : __('messages.pending') }}</td>
                        <td class="table-actions">
                            <a href="{{ route('seller.products.edit', $product) }}">{{ __('messages.edit_product') }}</a>
                            <form action="{{ route('seller.products.destroy', $product) }}" method="post" onsubmit="return confirm('{{ __('messages.product_unlisted') }}?')">
                                @csrf @method('delete')
                                <button class="text-button" type="submit">{{ __('messages.remove') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">{{ __('messages.no_products') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $products->links() }}
@endsection
