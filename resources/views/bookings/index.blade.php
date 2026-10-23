@extends('layouts.app')
@section('title', 'Appointments')
@section('content')
<div class="page-heading"><p class="eyebrow">Scheduling</p><h1>Appointments</h1>
<p class="muted">Customers request an hour; providers accept or reject. No response in 24h expires the request.</p></div>
<div class="card-grid">
@forelse ($bookings as $b)
<article class="panel listing-card">
<p class="eyebrow">#{{ $b->id }} · {{ ucfirst($b->status) }}</p>
<h3>{{ $b->date->toDateString() }} · {{ substr($b->start_time,0,5) }} ({{ $b->duration_minutes }} min)</h3>
<p>Customer: {{ $b->customer->name }} · Provider: {{ $b->provider->business_name ?: $b->provider->name }}</p>
@if ($b->notes)<p class="muted">{{ $b->notes }}</p>@endif
@if ($b->status === 'pending' && $b->provider_id === auth()->id())
<form class="form-actions" action="{{ route('bookings.respond', $b) }}" method="post">@csrf @method('patch')
<button class="button button-dark" name="status" value="accepted" type="submit">Accept</button>
<button class="button button-outline" name="status" value="rejected" type="submit">Reject</button>
</form>
@endif
</article>
@empty
<p class="empty-state">No appointments yet.</p>
@endforelse
</div>
{{ $bookings->links() }}
@endsection
