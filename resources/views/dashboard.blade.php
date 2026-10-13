@extends('layouts.app')

@section('title', __('messages.dashboard'))

@section('content')
    <div class="page-heading">
        <p class="eyebrow">{{ __('messages.dashboard') }}</p>
        <h1>{{ __('messages.welcome_back') }}, {{ auth()->user()->name }}</h1>
        <p>{{ __( 'messages.'.auth()->user()->role ) }} · {{ auth()->user()->location }}</p>
    </div>

    @if (auth()->user()->role === 'admin')
        <div class="action-grid">
            <a class="panel action-card" href="{{ route('admin.dashboard') }}"><span class="action-card-icon"><x-icon name="dashboard" /></span>{{ __('messages.admin') }} — {{ __('messages.dashboard') }}<x-icon name="arrow" class="action-card-arrow" /></a>
            <a class="panel action-card" href="{{ route('admin.users.index') }}"><span class="action-card-icon"><x-icon name="users" /></span>{{ __('messages.manage_users') }}<x-icon name="arrow" class="action-card-arrow" /></a>
            <a class="panel action-card" href="{{ route('admin.products.index') }}"><span class="action-card-icon"><x-icon name="package" /></span>{{ __('messages.manage_products') }}<x-icon name="arrow" class="action-card-arrow" /></a>
            <a class="panel action-card" href="{{ route('admin.orders.index') }}"><span class="action-card-icon"><x-icon name="shopping-bag" /></span>{{ __('messages.manage_orders') }}<x-icon name="arrow" class="action-card-arrow" /></a>
        </div>
    @else
        <div class="stat-grid">
            @if (auth()->user()->role === 'customer')
                <a class="panel stat-card" href="{{ route('projects.index') }}"><strong>{{ $requestsCount }}</strong><span>{{ __('messages.my_projects') }}</span></a>
                <a class="panel stat-card" href="{{ route('orders.index') }}"><strong>{{ $ordersCount }}</strong><span>{{ __('messages.orders') }}</span></a>
                @if (auth()->user()->serviceProviderProfile)
                    <a class="panel stat-card" href="{{ route('projects.index') }}"><strong>{{ $quotesCount }}</strong><span>{{ __('messages.my_quotes') }}</span></a>
                @endif
            @else
                <a class="panel stat-card" href="{{ route('seller.products.index') }}"><strong>{{ $productsCount }}</strong><span>{{ __('messages.my_products') }}</span></a>
                <a class="panel stat-card" href="{{ route('orders.index') }}"><strong>{{ $ordersCount }}</strong><span>{{ __('messages.orders') }}</span></a>
                <a class="panel stat-card" href="{{ route('projects.index') }}"><strong>{{ $quotesCount }}</strong><span>{{ __('messages.my_quotes') }}</span></a>
            @endif
            <a class="panel stat-card" href="{{ route('notifications.index') }}"><strong>{{ auth()->user()->unreadNotifications()->count() }}</strong><span>{{ __('messages.notifications') }}</span></a>
        </div>

        <div class="action-grid">
            <a class="panel action-card" href="{{ route('profile.edit') }}"><span class="action-card-icon"><x-icon name="user" /></span>{{ __('messages.edit_profile') }}<x-icon name="arrow" class="action-card-arrow" /></a>
            <a class="panel action-card" href="{{ route('messages.index') }}"><span class="action-card-icon"><x-icon name="chat" /></span>Messages<x-icon name="arrow" class="action-card-arrow" /></a>
            <a class="panel action-card" href="{{ route('bookings.index') }}"><span class="action-card-icon"><x-icon name="briefcase" /></span>Appointments<x-icon name="arrow" class="action-card-arrow" /></a>
            @if (auth()->user()->isServiceProvider())
                <a class="panel action-card" href="{{ route('schedules.index') }}"><span class="action-card-icon"><x-icon name="calendar" /></span>Availability schedule<x-icon name="arrow" class="action-card-arrow" /></a>
                <a class="panel action-card" href="{{ route('portfolio.index') }}"><span class="action-card-icon"><x-icon name="package" /></span>Portfolio<x-icon name="arrow" class="action-card-arrow" /></a>
            @endif
            @if (auth()->user()->role === 'customer')
                <a class="panel action-card" href="{{ route('projects.create') }}"><span class="action-card-icon"><x-icon name="briefcase" /></span>{{ __('messages.post_project') }}<x-icon name="arrow" class="action-card-arrow" /></a>
                <a class="panel action-card" href="{{ route('products.index') }}"><span class="action-card-icon"><x-icon name="package" /></span>{{ __('messages.browse_products') }}<x-icon name="arrow" class="action-card-arrow" /></a>
                @if (auth()->user()->serviceProviderProfile)
                    <a class="panel action-card" href="{{ route('projects.index') }}"><span class="action-card-icon"><x-icon name="briefcase" /></span>{{ __('messages.projects') }}<x-icon name="arrow" class="action-card-arrow" /></a>
                @endif
            @else
                <a class="panel action-card" href="{{ route('seller.products.create') }}"><span class="action-card-icon"><x-icon name="package" /></span>{{ __('messages.add_product') }}<x-icon name="arrow" class="action-card-arrow" /></a>
                <a class="panel action-card" href="{{ route('projects.index') }}"><span class="action-card-icon"><x-icon name="briefcase" /></span>{{ __('messages.projects') }}<x-icon name="arrow" class="action-card-arrow" /></a>
            @endif
            <a class="panel action-card" href="{{ route('community.index') }}"><span class="action-card-icon"><x-icon name="chat" /></span>{{ __('messages.community') }}<x-icon name="arrow" class="action-card-arrow" /></a>
        </div>
    @endif
@endsection
