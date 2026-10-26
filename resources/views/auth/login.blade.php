@extends('layouts.app')

@section('title', __('messages.login'))

@section('content')
    <section class="form-card narrow-card">
        <div class="page-heading">
            <h1>{{ __('messages.welcome_back') }}</h1>
        </div>
        <form action="{{ route('login.store') }}" method="post" class="stack-form">
            @csrf
            <label>{{ __('messages.email') }}
                <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" placeholder="{{ __('messages.placeholder_email') }}" required autofocus>
            </label>
            <label>{{ __('messages.password') }}
                <input name="password" type="password" autocomplete="current-password" placeholder="{{ __('messages.placeholder_password') }}" required>
            </label>
            <label class="check-row">
                <input name="remember" type="checkbox" value="1">
                <span>{{ __('messages.remember_me') }}</span>
            </label>
            <button class="button button-dark" type="submit">{{ __('messages.login') }}</button>
        </form>
        <p class="form-footnote"><a href="{{ route('password.request') }}">{{ __('messages.forgot_password') }}</a></p>
        <p class="form-footnote">{{ __('messages.no_account_yet') }} <a href="{{ route('register') }}">{{ __('messages.register') }}</a></p>
    </section>
@endsection
