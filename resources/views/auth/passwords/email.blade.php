@extends('layouts.app')

@section('title', __('messages.forgot_password'))

@section('content')
    <section class="form-card narrow-card">
        <div class="page-heading">
            <p class="eyebrow">{{ __('messages.brand') }}</p>
            <h1>{{ __('messages.forgot_password') }}</h1>
            <p>{{ __('messages.password_reset_instructions') }}</p>
        </div>
        @if (session('status'))
            <div class="notice notice-success" role="status">{{ session('status') }}</div>
        @endif
        <form action="{{ route('password.email') }}" method="post" class="stack-form">
            @csrf
            <label>{{ __('messages.email') }}
                <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            </label>
            <button class="button button-dark" type="submit">{{ __('messages.send_reset_link') }}</button>
        </form>
    </section>
@endsection
