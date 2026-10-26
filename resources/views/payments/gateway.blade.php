@extends('layouts.app')
@section('title', 'Secure online payment (demo gateway)')
@section('content')
<div class="page-heading"><p class="eyebrow">Eymar Pay · Demo</p><h1>Online payment</h1>
<p class="muted">Simulated gateway for your graduation demo. Card data is validated here and never stored — only a gateway token and the last 4 digits are saved.</p></div>
<div class="checkout-grid">
<section class="form-card">
<form class="stack-form" action="{{ route('payments.process') }}" method="post">
@csrf
<label>Cardholder name<input name="card_name" value="{{ old('card_name', auth()->user()->name) }}" maxlength="120" placeholder="{{ __('messages.placeholder_card_name') }}" required></label>
<label>Card number<input name="card_number" inputmode="numeric" placeholder="{{ __('messages.placeholder_card_number') }}" value="{{ old('card_number') }}" required><small class="muted">Use 4242… to approve, 4000 0000 0000 0002 to see a decline.</small></label>
<div class="form-columns">
<label>Expiry (MM/YY)<input name="expiry" placeholder="{{ __('messages.placeholder_expiry') }}" value="{{ old('expiry') }}" required></label>
<label>CVC<input name="cvc" inputmode="numeric" placeholder="{{ __('messages.placeholder_cvc') }}" required></label>
</div>
<button class="button button-dark" type="submit">Pay {{ number_format((float) $total, 2) }} JOD</button>
<a class="button button-outline" href="{{ route('orders.index') }}">Pay later / cancel</a>
</form>
</section>
<aside class="panel order-summary"><h2>Orders ({{ $orders->count() }})</h2>
@foreach ($orders as $order)
<p class="summary-line"><span>Order #{{ $order->id }} · {{ $order->seller->business_name ?: $order->seller->name }}</span><strong>{{ number_format((float) $order->total, 2) }} JOD</strong></p>
@endforeach
<p class="muted">After approval each seller is notified to confirm. On rejection of an order the payment is marked for refund.</p>
</aside>
</div>
@endsection
