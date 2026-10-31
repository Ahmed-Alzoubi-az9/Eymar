@extends('layouts.app')
@section('title', __('messages.messages_label'))
@section('content')
<div class="page-heading"><p class="eyebrow">{{ __('messages.inbox') }}</p><h1>{{ __('messages.messages_label') }}</h1>
<p class="muted">{{ __('messages.messages_intro') }}</p></div>
<div class="card-grid">
@forelse ($threads as $thread)
<article class="panel listing-card">
<h3>{{ $thread['user']->business_name ?: $thread['user']->name }}</h3>
<p class="muted">{{ $thread['last']?->body ? \Illuminate\Support\Str::limit($thread['last']->body, 80) : '' }}</p>
@if ($thread['unread'] > 0)<p class="eyebrow">{{ __('messages.unread_count', ['count' => $thread['unread']]) }}</p>@endif
<a class="button button-outline" href="{{ route('messages.show', $thread['user']) }}">{{ __('messages.open_chat') }}</a>
</article>
@empty
<p class="empty-state">{{ __('messages.no_conversations') }}</p>
@endforelse
</div>
@endsection