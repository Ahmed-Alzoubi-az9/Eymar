@extends('layouts.app')

@section('title', __('messages.register'))

@section('content')
    <section class="form-card narrow-card">
        <div class="page-heading">
            <h1>{{ __('messages.create_account') }}</h1>
        </div>
        <form action="{{ route('register.store') }}" method="post" class="stack-form" enctype="multipart/form-data">
            @csrf
            <label>{{ __('messages.name') }}
                <input name="name" value="{{ old('name') }}" autocomplete="name" maxlength="120" required>
            </label>
            <label>{{ __('messages.username') }}
                <input name="username" value="{{ old('username') }}" autocomplete="username" minlength="3" maxlength="32" required>
            </label>
            <label>{{ __('messages.email') }}
                <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" placeholder="{{ __('messages.email') }}" required>
            </label>
            <label>{{ __('messages.account_type') }}
                <select name="role" data-account-role required>
                    @foreach (['customer', 'company'] as $role)
                        <option value="{{ $role }}" @selected(old('role', 'customer') === $role)>{{ __("messages.$role") }}</option>
                    @endforeach
                </select>
            </label>
            <label>{{ __('messages.phone') }}
                <input name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" maxlength="24" required>
            </label>
            <label>{{ __('messages.location') }}
                <input name="location" value="{{ old('location') }}" maxlength="160">
            </label>
            <label>{{ __('messages.address') }}
                <textarea name="address" rows="2" maxlength="2000">{{ old('address') }}</textarea>
            </label>
            <label>{{ __('messages.age') }}
                <input name="age" type="number" min="13" max="120" value="{{ old('age') }}">
            </label>
            <label>{{ __('messages.profile_image') }}
                <input name="profile_image" type="file" accept="image/jpeg,image/png,image/webp">
            </label>
            <fieldset class="stack-form" data-company-fields @hidden(old('role', 'customer') !== 'company')>
                <legend>{{ __('messages.company_profile') }}</legend>
                <label>{{ __('messages.business_name') }}
                    <input name="business_name" data-company-required value="{{ old('business_name') }}" maxlength="160">
                </label>
                <label>{{ __('messages.company_owner') }}
                    <input name="company_owner" data-company-required value="{{ old('company_owner') }}" maxlength="120">
                </label>
                <label>{{ __('messages.established_date') }}
                    <input name="established_date" type="date" value="{{ old('established_date') }}" max="{{ now()->toDateString() }}">
                </label>
                <label>{{ __('messages.company_logo') }}
                    <input name="company_logo" type="file" accept="image/jpeg,image/png,image/webp">
                </label>
            </fieldset>
            <label>{{ __('messages.password') }}
                <input name="password" type="password" autocomplete="new-password" minlength="12" placeholder="{{ __('messages.password') }}" required>
            </label>
            <label>{{ __('messages.confirm_password') }}
                <input name="password_confirmation" type="password" autocomplete="new-password" placeholder="{{ __('messages.confirm_password') }}" required>
            </label>
            <button class="button button-dark" type="submit">{{ __('messages.register') }}</button>
        </form>
        <p class="form-footnote">{{ __('messages.have_account') }} <a href="{{ route('login') }}">{{ __('messages.login') }}</a></p>
    </section>
@endsection
