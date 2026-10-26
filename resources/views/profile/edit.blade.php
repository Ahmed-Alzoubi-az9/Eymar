@extends('layouts.app')

@section('title', __('messages.edit_profile'))

@section('content')
    <div class="profile-page-heading">
        <div>
            <p class="eyebrow"><x-icon name="settings" />{{ __('messages.account_settings') }}</p>
            <h1>{{ __('messages.edit_profile') }}</h1>
            <p>{{ __('messages.profile_settings_intro') }}</p>
        </div>
        <a class="button button-outline" href="{{ route('dashboard') }}"><x-icon name="dashboard" />{{ __('messages.dashboard') }}</a>
    </div>

    <form action="{{ route('profile.update') }}" method="post" class="profile-settings-layout" enctype="multipart/form-data">
        @csrf
        @method('put')

        <aside class="profile-settings-aside">
            <section class="panel profile-summary">
                <div class="profile-avatar">
                    @if ($user->profile_image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($user->profile_image_path) }}" alt="{{ __('messages.profile_image') }}" data-profile-avatar>
                    @else
                        <span data-profile-avatar-fallback>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</span>
                        <img alt="{{ __('messages.profile_image') }}" data-profile-avatar hidden>
                    @endif
                </div>
                <h2>{{ $user->name }}</h2>
                <p class="profile-summary-username">{{ '@'.$user->username }}</p>
                <span class="profile-role-badge"><x-icon name="check" />{{ __("messages.{$user->role}") }}</span>
                @if ($user->location)
                    <p class="profile-summary-location"><x-icon name="pin" />{{ $user->location }}</p>
                @endif
            </section>

            <nav class="panel profile-settings-nav" aria-label="{{ __('messages.profile_sections') }}">
                <a href="#personal-details"><x-icon name="user" />{{ __('messages.personal_details') }}</a>
                <a href="#profile-media"><x-icon name="camera" />{{ __('messages.profile_media') }}</a>
                @if ($user->role === 'customer')
                    <a href="#professional-details"><x-icon name="construction" />{{ __('messages.professional_profile') }}</a>
                @elseif ($user->role === 'company')
                    <a href="#company-details"><x-icon name="building" />{{ __('messages.company_profile') }}</a>
                @endif
            </nav>
        </aside>

        <div class="profile-settings-content">
            <fieldset class="panel profile-section stack-form" id="personal-details">
                <legend><span class="profile-section-icon"><x-icon name="user" /></span>{{ __('messages.personal_details') }}</legend>
                <p class="profile-section-description">{{ __('messages.personal_details_help') }}</p>
                <div class="form-columns">
                    <label>{{ __('messages.name') }}<input name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required maxlength="120" placeholder="{{ __('messages.placeholder_name') }}"></label>
                    <label>{{ __('messages.username') }}<input name="username" value="{{ old('username', $user->username) }}" autocomplete="username" required minlength="3" maxlength="32" placeholder="{{ __('messages.placeholder_username') }}"></label>
                </div>
                <div class="form-columns">
                    <label>{{ __('messages.email') }}<input name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" required maxlength="255" placeholder="{{ __('messages.placeholder_email') }}"></label>
                    <label>{{ __('messages.phone') }}<input name="phone" type="tel" value="{{ old('phone', $user->phone) }}" autocomplete="tel" required maxlength="24" placeholder="{{ __('messages.placeholder_phone') }}"></label>
                </div>
                <div class="form-columns">
                    <label>{{ __('messages.location') }}<input name="location" value="{{ old('location', $user->location) }}" maxlength="160" placeholder="{{ __('messages.placeholder_location') }}"></label>
                    @if ($user->role === 'customer')
                        <label>{{ __('messages.age') }}<input name="age" type="number" min="13" max="120" value="{{ old('age', $user->age) }}" placeholder="{{ __('messages.placeholder_age') }}"></label>
                    @endif
                </div>
                <label>{{ __('messages.address') }}<textarea name="address" rows="3" maxlength="2000" placeholder="{{ __('messages.placeholder_address') }}">{{ old('address', $user->address) }}</textarea></label>
            </fieldset>

            <fieldset class="panel profile-section stack-form" id="profile-media">
                <legend><span class="profile-section-icon"><x-icon name="camera" /></span>{{ __('messages.profile_media') }}</legend>
                <p class="profile-section-description">{{ __('messages.profile_media_help') }}</p>
                <div class="profile-media-grid">
                    <div class="profile-media-preview profile-avatar">
                        @if ($user->profile_image_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($user->profile_image_path) }}" alt="{{ __('messages.profile_image') }}" data-profile-avatar>
                        @else
                            <span data-profile-avatar-fallback>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</span>
                            <img alt="{{ __('messages.profile_image') }}" data-profile-avatar hidden>
                        @endif
                    </div>
                    <label class="file-upload-card">
                        <x-icon name="camera" />
                        <span>{{ __('messages.profile_image') }}</span>
                        <small>{{ __('messages.image_upload_hint') }}</small>
                        <input name="profile_image" type="file" accept="image/jpeg,image/png,image/webp" data-profile-image-input>
                    </label>
                </div>
                @if ($user->role === 'company')
                    <div class="company-media-control">
                        <div class="company-logo-preview" data-company-logo-preview>
                            @if ($user->company_logo_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($user->company_logo_path) }}" alt="{{ __('messages.company_logo') }}">
                            @else
                                <x-icon name="building" />
                            @endif
                        </div>
                        <label class="file-upload-card">
                            <x-icon name="camera" />
                            <span>{{ __('messages.company_logo') }}</span>
                            <small>{{ __('messages.image_upload_hint') }}</small>
                            <input name="company_logo" type="file" accept="image/jpeg,image/png,image/webp" data-company-logo-input>
                        </label>
                    </div>
                @endif
            </fieldset>

            @if ($user->role === 'customer')
                <fieldset class="panel profile-section stack-form" id="professional-details">
                    <legend><span class="profile-section-icon"><x-icon name="construction" /></span>{{ __('messages.professional_profile') }}</legend>
                    <p class="profile-section-description">{{ __('messages.professional_profile_help') }}</p>
                    <label>{{ __('messages.professional_type') }}
                        <select name="provider_type" data-provider-type>
                            <option value="">{{ __('messages.not_offering_services') }}</option>
                            @foreach (['contractor', 'craftsman'] as $type)
                                <option value="{{ $type }}" @selected(old('provider_type', $user->serviceProviderProfile?->provider_type) === $type)>{{ __("messages.$type") }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="provider-fields" data-provider-fields>
                        <div class="form-columns">
                            <label>{{ __('messages.experience_years') }}<input name="experience_years" type="number" min="0" max="70" value="{{ old('experience_years', $user->serviceProviderProfile?->experience_years) }}" placeholder="{{ __('messages.placeholder_experience') }}" data-provider-required></label>
                            <label>{{ __('messages.pricing_basis') }}
                                <select name="pricing_basis" data-provider-required>
                                    <option value="">{{ __('messages.choose_pricing_basis') }}</option>
                                    <option value="hourly" @selected(old('pricing_basis', $user->serviceProviderProfile?->pricing_basis) === 'hourly')>{{ __('messages.hourly') }}</option>
                                    <option value="unit" @selected(old('pricing_basis', $user->serviceProviderProfile?->pricing_basis) === 'unit')>{{ __('messages.per_unit') }}</option>
                                </select>
                            </label>
                        </div>
                        <label>{{ __('messages.service_rate') }}<input name="service_rate" type="number" min="0.01" step="0.01" value="{{ old('service_rate', $user->serviceProviderProfile?->service_rate) }}" placeholder="{{ __('messages.placeholder_rate') }}" data-provider-required></label>
                        <label>{{ __('messages.previous_work') }}<textarea name="previous_work" rows="4" maxlength="5000" placeholder="{{ __('messages.placeholder_previous_work') }}">{{ old('previous_work', $user->serviceProviderProfile?->previous_work) }}</textarea></label>
                        <label class="check-row"><input name="is_available" type="checkbox" value="1" @checked(old('is_available', $user->serviceProviderProfile?->is_available ?? true))> <span>{{ __('messages.available_for_work') }}</span></label>
                    </div>
                </fieldset>
            @elseif ($user->role === 'company')
                <fieldset class="panel profile-section stack-form" id="company-details">
                    <legend><span class="profile-section-icon"><x-icon name="building" /></span>{{ __('messages.company_profile') }}</legend>
                    <p class="profile-section-description">{{ __('messages.company_profile_help') }}</p>
                    <div class="form-columns">
                        <label>{{ __('messages.business_name') }}<input name="business_name" value="{{ old('business_name', $user->business_name) }}" required maxlength="160" placeholder="{{ __('messages.placeholder_company_name') }}"></label>
                        <label>{{ __('messages.company_owner') }}<input name="company_owner" value="{{ old('company_owner', $user->company_owner) }}" required maxlength="120" placeholder="{{ __('messages.placeholder_name') }}"></label>
                    </div>
                    <label>{{ __('messages.established_date') }}<input name="established_date" type="date" value="{{ old('established_date', $user->established_date?->toDateString()) }}" max="{{ now()->toDateString() }}"></label>
                    <label>{{ __('messages.bio') }}<textarea name="bio" rows="4" maxlength="2000" placeholder="{{ __('messages.placeholder_description') }}">{{ old('bio', $user->bio) }}</textarea></label>
                </fieldset>
            @endif

            <div class="profile-save-bar">
                <p><x-icon name="shield" />{{ __('messages.profile_save_note') }}</p>
                <button class="button button-dark" type="submit"><x-icon name="check" />{{ __('messages.save') }}</button>
            </div>
        </div>
    </form>
@endsection
