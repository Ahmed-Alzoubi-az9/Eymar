@extends('layouts.app')

@section('title', $project->title)

@section('content')
    <article class="panel project-detail">
        <p class="eyebrow">{{ $project->category->display_name }} · {{ __('messages.'.strtolower($project->status)) }}</p>
        <h1>{{ $project->title }}</h1>
        <p>{{ __('messages.customer_label') }}: {{ $project->customer->name }} · {{ $project->location }}</p>
        <p>{{ $project->description }}</p>
        @if ($project->budget_min || $project->budget_max)
            <p>{{ __('messages.budget_min') }}: {{ $project->budget_min ?? '—' }} · {{ __('messages.budget_max') }}: {{ $project->budget_max ?? '—' }}</p>
        @endif
        @if ($project->acceptedProvider)
            <p>{{ __('messages.professionals') }}: {{ $project->acceptedProvider->business_name ?: $project->acceptedProvider->name }}</p>
        @endif
    </article>

    @if (auth()->user()->id === $project->customer_id && $project->status === 'open')
        <section class="section-block">
            <h2>{{ __('messages.quotes') }} ({{ $quotes->count() }})</h2>
            <div class="card-grid">
                @forelse ($quotes as $quote)
                    <article class="panel listing-card">
                        <h3>{{ $quote->provider->business_name ?: $quote->provider->name }}</h3>
                        <p>{{ __('messages.quote_amount') }}: {{ number_format((float) $quote->amount, 2) }}</p>
                        @if ($quote->estimated_days)<p>{{ __('messages.estimated_days') }}: {{ $quote->estimated_days }}</p>@endif
                        <p>{{ $quote->message }}</p>
                        @if ($quote->status === 'pending')
                            <form action="{{ route('projects.quotes.accept', [$project, $quote]) }}" method="post">
                                @csrf
                                <button class="button button-dark" type="submit">{{ __('messages.accept_quote') }}</button>
                            </form>
                        @else
                            <p class="eyebrow">{{ __('messages.'.strtolower($quote->status)) }}</p>
                        @endif
                    </article>
                @empty
                    <p class="empty-state">{{ __('messages.quotes') }} — 0</p>
                @endforelse
            </div>
        </section>
    @elseif (auth()->user()->isServiceProvider() && $project->status === 'open' && $project->customer_id !== auth()->id() && ! $project->quotes->contains('provider_id', auth()->id()))
        <section class="form-card">
            <h2>{{ __('messages.send_quote') }}</h2>
            <form class="stack-form" action="{{ route('projects.quotes.store', $project) }}" method="post">
                @csrf
                <label>{{ __('messages.quote_amount') }}<input type="number" name="amount" min="0.01" step="0.01" placeholder="{{ __('messages.placeholder_price') }}" required></label>
                <label>{{ __('messages.estimated_days') }}<input type="number" name="estimated_days" min="1" max="3650" placeholder="{{ __('messages.placeholder_estimated_days') }}"></label>
                <label>{{ __('messages.quote_message') }}<textarea name="message" rows="4" maxlength="3000" placeholder="{{ __('messages.placeholder_quote_message') }}" required></textarea></label>
                <button class="button button-dark" type="submit">{{ __('messages.send_quote') }}</button>
            </form>
        </section>
    @else
        <section class="section-block">
            <h2>{{ __('messages.quotes') }}</h2>
            <div class="card-grid">
                @foreach ($quotes->where('status', 'accepted') as $quote)
                    <article class="panel listing-card">
                        <h3>{{ $quote->provider->business_name ?: $quote->provider->name }}</h3>
                        <p>{{ number_format((float) $quote->amount, 2) }} · {{ $quote->message }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if ($project->status === 'awarded' && $project->accepted_provider_id === auth()->id())
        <form class="status-form" action="{{ route('projects.status', $project) }}" method="post">
            @csrf @method('patch')<input type="hidden" name="status" value="in_progress">
            <button class="button button-dark" type="submit">{{ __('messages.start_project') }}</button>
        </form>
    @elseif ($project->status === 'in_progress' && $project->customer_id === auth()->id())
        <form class="status-form" action="{{ route('projects.status', $project) }}" method="post">
            @csrf @method('patch')<input type="hidden" name="status" value="completed">
            <button class="button button-dark" type="submit">{{ __('messages.complete_project') }}</button>
        </form>
    @elseif ($project->status === 'open' && $project->customer_id === auth()->id())
        <form class="status-form" action="{{ route('projects.status', $project) }}" method="post">
            @csrf @method('patch')<input type="hidden" name="status" value="cancelled">
            <button class="button button-outline" type="submit">{{ __('messages.cancel_project') }}</button>
        </form>
    @endif

    <section class="section-block">
        <h2>Review provider</h2>
        @if ($project->review)
            <p><strong>{{ $project->review->rating }}/5</strong> — {{ $project->review->comment }}</p>
        @elseif ($project->customer_id === auth()->id() && $project->status === 'completed' && $project->accepted_provider_id)
            <form class="stack-form form-card" action="{{ route('projects.reviews.store', $project) }}" method="post">
                @csrf
                <label>Rating (1–5)<input type="number" name="rating" min="1" max="5" placeholder="{{ __('messages.placeholder_review_rating') }}" required></label>
                <label>Comment<textarea name="comment" rows="3" maxlength="2000" placeholder="{{ __('messages.placeholder_comment') }}"></textarea></label>
                <button class="button button-dark" type="submit">Submit review</button>
            </form>
        @else
            <p class="muted">Reviews open after the project is completed.</p>
        @endif
    </section>
@endsection
