@extends('layouts.app')

@section('title', __('messages.professionals'))

@section('content')
    <div class="page-heading">
        <h1>{{ __('messages.browse_professionals') }}</h1>
    </div>
    <form class="search-form search-form--pros" method="get" action="{{ route('professionals.index') }}">
        <input name="q" value="{{ request('q') }}" placeholder="{{ __('messages.search_placeholder') }}" aria-label="{{ __('messages.search') }}" maxlength="100">
        <input name="location" value="{{ request('location') }}" placeholder="{{ __('messages.placeholder_location') }}" aria-label="{{ __('messages.location') }}" maxlength="160">
        <select name="provider_type" aria-label="{{ __('messages.professional_type') }}">
            <option value="">{{ __('messages.all_professionals') }}</option>
            @foreach (['contractor', 'craftsman'] as $type)
                <option value="{{ $type }}" @selected(request('provider_type') === $type)>{{ __("messages.$type") }}</option>
            @endforeach
        </select>
        <input type="number" name="min_experience" min="0" max="70" value="{{ request('min_experience') }}" placeholder="{{ __('messages.min_experience') }}" aria-label="{{ __('messages.min_experience') }}">
        <input type="number" name="max_experience" min="0" max="70" value="{{ request('max_experience') }}" placeholder="{{ __('messages.max_experience') }}" aria-label="{{ __('messages.max_experience') }}">
        <select name="pricing_basis" aria-label="{{ __('messages.pricing_basis') }}">
            <option value="">{{ __('messages.choose_pricing_basis') }}</option>
            <option value="hourly" @selected(request('pricing_basis') === 'hourly')>{{ __('messages.hourly') }}</option>
            <option value="unit" @selected(request('pricing_basis') === 'unit')>{{ __('messages.per_unit') }}</option>
        </select>
        <input type="number" name="min_rate" min="0" step="0.01" value="{{ request('min_rate') }}" placeholder="{{ __('messages.min_rate') }}" aria-label="{{ __('messages.min_rate') }}">
        <input type="number" name="max_rate" min="0" step="0.01" value="{{ request('max_rate') }}" placeholder="{{ __('messages.max_rate') }}" aria-label="{{ __('messages.max_rate') }}">
        <select name="availability" aria-label="{{ __('messages.availability') }}">
            <option value="">{{ __('messages.all_availability') }}</option>
            <option value="available" @selected(request('availability') === 'available')>{{ __('messages.available') }}</option>
            <option value="unavailable" @selected(request('availability') === 'unavailable')>{{ __('messages.unavailable') }}</option>
        </select>
        <button class="button button-dark" type="submit">{{ __('messages.search') }}</button>
    </form>
    <div class="card-grid">
        @forelse ($providers as $provider)
            <article class="panel listing-card">
                <p class="eyebrow">{{ __("messages.".($provider->role === 'company' ? 'company' : $provider->serviceProviderProfile->provider_type)) }}</p>
                <h2>{{ $provider->business_name ?: $provider->name }}</h2>
                <p>{{ $provider->location }}</p>
                <p class="muted">{{ \Illuminate\Support\Str::limit($provider->bio ?: $provider->name, 150) }}</p>
                <a class="button button-outline" href="{{ route('professionals.show', $provider) }}">{{ __('messages.view_profile') }}</a>
            </article>
        @empty
            <p class="empty-state">{{ __('messages.professionals') }} — {{ __('messages.no_projects') }}</p>
        @endforelse
    </div>
    {{ $providers->links() }}
@endsection
