@extends('layouts.app')
@section('title', 'Verify email')
@section('content')
<section class="form-card">
    <p class="eyebrow">Eymar</p>
    <h1>Verify your email</h1>
    <p class="muted">We sent a verification link to {{ auth()->user()->email }}. You must verify before using orders, projects and messages.</p>
    <form class="stack-form" action="{{ route('verification.send') }}" method="post">
        @csrf
        <button class="button button-dark" type="submit">Resend verification link</button>
    </form>
</section>
@endsection
