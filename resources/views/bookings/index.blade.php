@extends('layouts.app')
@section('title', __('messages.appointments'))
@section('content')
<div class="page-heading"><p class="eyebrow">{{ __('messages.scheduling') }}</p><h1>{{ __('messages.appointments') }}</h1>
<p class="muted">{{ __('messages.appointments_intro') }}</p></div>
<div class="card-grid">
@forelse ($bookings as $b)
<article class="panel listing-card">
<p class="eyebrow">#{{ $b->id }} · {{ __('messages.' . $b->status) }}</p>
<h3>{{ $b->date->toDateString() }} · {{ substr($b->start_time,0,5) }} ({{ $b->duration_minutes }} {{ __('messages.min') }})</h3>
<p>{{ __('messages.customer') }}: {{ $b->customer->name }} · {{ __('messages.provider_label') }}: {{ $b->provider->business_name ?: $b->provider->name }}</p>
@if ($b->notes)<p class="muted">{{ $b->notes }}</p>@endif
@if ($b->status === 'pending' && $b->provider_id === auth()->id())
<form class="form-actions" action="{{ route('bookings.respond', $b) }}" method="post">@csrf @method('patch')
<button class="button button-dark" name="status" value="accepted" type="submit">{{ __('messages.accept') }}</button>
<button class="button button-outline" name="status" value="rejected" type="submit">{{ __('messages.reject') }}</button>
</form>
@endif
</article>
@empty
<p class="empty-state">{{ __('messages.no_appointments') }}</p>
@endforelse
</div>
{{ $bookings->links() }}
@endsection