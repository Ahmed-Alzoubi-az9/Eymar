@extends('layouts.app')

@section('title', $provider->business_name ?: $provider->name)

@section('content')
    <section class="panel profile-card">
        @if ($provider->role === 'company' && $provider->company_logo_path)
            <img class="company-logo" src="{{ \Illuminate\Support\Facades\Storage::url($provider->company_logo_path) }}" alt="{{ __('messages.company_logo') }} — {{ $provider->business_name }}">
        @elseif ($provider->profile_image_path)
            <img class="profile-image" src="{{ \Illuminate\Support\Facades\Storage::url($provider->profile_image_path) }}" alt="{{ __('messages.profile_image') }} — {{ $provider->name }}">
        @endif
        <p class="eyebrow">{{ __("messages.".($provider->role === 'company' ? 'company' : $provider->serviceProviderProfile->provider_type)) }}</p>
        <h1>{{ $provider->business_name ?: $provider->name }}</h1>
        <p>{{ $provider->location }}{{ $provider->phone ? ' · '.$provider->phone : '' }}</p>
        <p><a href="mailto:{{ $provider->email }}">{{ $provider->email }}</a></p>
        @if (isset($averageRating))
            <p><strong>Rating: {{ $averageRating ?? '—' }}/5</strong> ({{ $reviewsCount }} reviews)</p>
        @endif
        @if ($provider->role === 'company')
            @if ($provider->company_owner)<p>{{ __('messages.company_owner') }}: {{ $provider->company_owner }}</p>@endif
            @if ($provider->established_date)<p>{{ __('messages.established_date') }}: {{ $provider->established_date->toDateString() }}</p>@endif
        @endif
        <p>{{ $provider->bio }}</p>
        @if ($provider->serviceProviderProfile)
            <p>{{ __('messages.experience_years') }}: {{ $provider->serviceProviderProfile->experience_years ?? '—' }}</p>
            <p>{{ __('messages.pricing_basis') }}: {{ $provider->serviceProviderProfile->pricing_basis ? __('messages.'.$provider->serviceProviderProfile->pricing_basis) : '—' }} · {{ __('messages.service_rate') }}: {{ $provider->serviceProviderProfile->service_rate ?? '—' }}</p>
            <p>{{ __('messages.availability') }}: {{ $provider->serviceProviderProfile->is_available ? __('messages.available') : __('messages.unavailable') }}</p>
            <p>{{ $provider->serviceProviderProfile->previous_work }}</p>
        @endif
        @auth
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px">
                @if (auth()->id() !== $provider->id)
                    <a class="button button-outline" href="{{ route('messages.show', $provider) }}">Message</a>
                @endif
                @if (auth()->user()->role === 'customer')
                    <a class="button button-dark" href="{{ route('projects.create') }}">{{ __('messages.post_project') }}</a>
                @endif
            </div>
            @if (auth()->id() !== $provider->id && $provider->serviceProviderProfile)
                <form class="stack-form form-card booking-request-form" action="{{ route('bookings.store', $provider) }}" method="post">
                    @csrf
                    <h3>Request appointment (hourly)</h3>
                    <div class="form-columns form-columns--3">
                        <label>Date<input type="date" name="date" min="{{ now()->toDateString() }}" required></label>
                        <label>Start<input type="time" name="start_time" required></label>
                        <label>Minutes<input type="number" name="duration_minutes" min="30" max="480" step="30" value="60" placeholder="{{ __('messages.placeholder_quantity') }}" required></label>
                    </div>
                    <label>Notes<textarea name="notes" rows="2" maxlength="1000" placeholder="{{ __('messages.placeholder_description') }}"></textarea></label>
                    <button class="button button-dark" type="submit">Send booking request</button>
                </form>
            @endif
        @else
            <a class="button button-dark" href="{{ route('register') }}">{{ __('messages.register') }}</a>
        @endauth
    </section>

    @if ($provider->providerSchedules->isNotEmpty())
        <div class="section-heading"><h2>Upcoming availability</h2></div>
        <div class="table-wrap"><table><thead><tr><th>Date</th><th>Hours</th><th>Status</th></tr></thead><tbody>
            @foreach ($provider->providerSchedules as $s)
                <tr><td>{{ $s->date->toDateString() }}</td><td>{{ substr($s->start_time,0,5) }}–{{ substr($s->end_time,0,5) }}</td><td>{{ $s->is_available ? 'Free' : 'Busy' }}</td></tr>
            @endforeach
        </tbody></table></div>
    @endif

    @if ($provider->portfolioItems->isNotEmpty())
        <div class="section-heading"><h2>Portfolio</h2></div>
        <div class="card-grid">
            @foreach ($provider->portfolioItems as $item)
                <article class="panel listing-card">
                    <h3>{{ $item->title }}</h3>
                    @if ($item->description)<p class="muted">{{ \Illuminate\Support\Str::limit($item->description, 120) }}</p>@endif
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px">
                        @foreach ($item->images as $img)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($img->image_path) }}" alt="{{ $item->title }}" style="width:100%;height:80px;object-fit:cover;border-radius:8px">
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    <div class="section-heading"><h2>Reviews ({{ $reviewsCount ?? $provider->reviewsReceived->count() }})</h2></div>
    <div class="card-grid">
        @forelse ($provider->reviewsReceived as $review)
            <article class="panel listing-card">
                <p><strong>{{ $review->rating }}/5</strong> · {{ $review->reviewer?->name }}</p>
                @if ($review->comment)<p>{{ $review->comment }}</p>@endif
                <p class="muted"><small>{{ $review->created_at->format('Y-m-d') }}</small></p>
            </article>
        @empty
            <p class="empty-state">No reviews yet — be the first after a completed order or project.</p>
        @endforelse
    </div>

    <div class="section-heading">
        <h2>{{ __('messages.products') }}</h2>
    </div>
    <div class="card-grid">
        @forelse ($provider->products as $product)
            <article class="panel listing-card">
                <h3>{{ $product->name }}</h3>
                <p>{{ number_format((float) $product->price, 2) }} {{ __('messages.currency') }}</p>
                <a class="button button-outline" href="{{ route('products.show', $product) }}">{{ __('messages.view_product') }}</a>
            </article>
        @empty
            <p class="empty-state">{{ __('messages.no_products') }}</p>
        @endforelse
    </div>
@endsection
