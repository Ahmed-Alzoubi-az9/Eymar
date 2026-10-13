@extends('layouts.app')

@section('title', __('messages.manage_products'))

@section('content')
    <div class="page-heading"><p class="eyebrow">{{ __('messages.admin') }}</p><h1>{{ __('messages.manage_products') }}</h1></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>{{ __('messages.product_name') }}</th><th>{{ __('messages.seller') }}</th><th>{{ __('messages.price') }}</th><th>{{ __('messages.stock') }}</th><th>{{ __('messages.user_status') }}</th><th>{{ __('messages.actions') }}</th></tr></thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td><td>{{ $product->seller->business_name ?: $product->seller->name }}</td>
                        <td>{{ number_format((float) $product->price, 2) }} {{ __('messages.currency') }}</td><td>{{ $product->stock }}</td>
                        <td>{{ $product->is_active ? __('messages.active') : __('messages.pending') }}</td>
                        <td>
                            <form action="{{ route('admin.products.status', $product) }}" method="post">
                                @csrf @method('patch')
                                <input type="hidden" name="is_active" value="{{ $product->is_active ? '0' : '1' }}">
                                <button class="text-button" type="submit">{{ $product->is_active ? __('messages.unpublish') : __('messages.approve') }}</button>
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
