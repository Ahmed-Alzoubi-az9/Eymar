@extends('layouts.app')

@section('title', __('messages.reset_password'))

@section('content')
    <section class="form-card narrow-card">
        <div class="page-heading">
            <h1>{{ __('messages.reset_password') }}</h1>
        </div>
        <form action="{{ route('password.update') }}" method="post" class="stack-form">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <label>{{ __('messages.email') }}
                <input name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" placeholder="{{ __('messages.placeholder_email') }}" required>
            </label>
            <label>{{ __('messages.password') }}
                <input name="password" type="password" autocomplete="new-password" minlength="12" placeholder="{{ __('messages.placeholder_new_password') }}" required>
            </label>
            <label>{{ __('messages.confirm_password') }}
                <input name="password_confirmation" type="password" autocomplete="new-password" minlength="12" placeholder="{{ __('messages.confirm_password') }}" required>
            </label>
            <button class="button button-dark" type="submit">{{ __('messages.reset_password') }}</button>
        </form>
    </section>
@endsection
