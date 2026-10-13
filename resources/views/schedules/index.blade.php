@extends('layouts.app')
@section('title', 'Availability schedule')
@section('content')
<div class="page-heading"><p class="eyebrow">Contractor / Craftsman</p><h1>Hourly availability</h1>
<p class="muted">Define the hours customers can book. Overlapping slots are rejected. Pending bookings expire after 24h.</p></div>
<section class="form-card">
<form class="stack-form" action="{{ route('schedules.store') }}" method="post">
@csrf
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
<label>Date<input type="date" name="date" min="{{ now()->toDateString() }}" required></label>
<label>Start<input type="time" name="start_time" required></label>
<label>End<input type="time" name="end_time" required></label>
</div>
<label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="is_available" value="1" checked> Available for booking</label>
<button class="button button-dark" type="submit">Save slot</button>
</form>
</section>
<div class="table-wrap"><table><thead><tr><th>Date</th><th>Start</th><th>End</th><th>Available</th><th></th></tr></thead><tbody>
@forelse ($schedules as $s)
<tr><td>{{ $s->date->toDateString() }}</td><td>{{ substr($s->start_time,0,5) }}</td><td>{{ substr($s->end_time,0,5) }}</td><td>{{ $s->is_available ? 'Yes' : 'Busy' }}</td>
<td><form action="{{ route('schedules.destroy', $s) }}" method="post">@csrf @method('delete')<button class="button button-outline" type="submit">Remove</button></form></td></tr>
@empty
<tr><td colspan="5">No slots yet. Add your working hours above.</td></tr>
@endforelse
</tbody></table></div>
{{ $schedules->links() }}
@endsection
