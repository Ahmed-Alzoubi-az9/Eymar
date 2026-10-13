@extends('layouts.app')

@section('title', __('messages.admin'))

@section('content')
    <div class="page-heading"><p class="eyebrow">{{ __('messages.admin') }}</p><h1>{{ __('messages.dashboard') }}</h1></div>
    <div class="stat-grid">
        <a class="panel stat-card" href="{{ route('admin.users.index') }}"><strong>{{ $usersCount }}</strong><span>{{ __('messages.users_count') }}</span></a>
        <a class="panel stat-card" href="{{ route('admin.products.index') }}"><strong>{{ $pendingProductsCount }}</strong><span>{{ __('messages.pending_products') }}</span></a>
        <a class="panel stat-card" href="{{ route('admin.orders.index') }}"><strong>{{ $ordersCount }}</strong><span>{{ __('messages.orders_count') }}</span></a>
        <a class="panel stat-card" href="{{ route('projects.index') }}"><strong>{{ $openProjectsCount }}</strong><span>{{ __('messages.projects_count') }}</span></a>
    </div>
    <div class="action-grid">
        <a class="panel action-card" href="{{ route('admin.users.index') }}">{{ __('messages.manage_users') }}</a>
        <a class="panel action-card" href="{{ route('admin.products.index') }}">{{ __('messages.manage_products') }}</a>
        <a class="panel action-card" href="{{ route('admin.orders.index') }}">{{ __('messages.manage_orders') }}</a>
        <a class="panel action-card" href="{{ route('admin.posts.index') }}">Moderate community posts</a>
        <a class="panel action-card" href="{{ route('admin.projects.index') }}">Moderate projects</a>
    </div>
@endsection
