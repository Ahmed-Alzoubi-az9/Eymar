@extends('layouts.app')
@section('title', 'Messages')
@section('content')
<div class="page-heading"><p class="eyebrow">Inbox</p><h1>Messages</h1>
<p class="muted">Direct contact with companies, contractors and craftsmen. Threads refresh in real time.</p></div>
<div class="card-grid">
@forelse ($threads as $thread)
<article class="panel listing-card">
<h3>{{ $thread['user']->business_name ?: $thread['user']->name }}</h3>
<p class="muted">{{ $thread['last']?->body ? \Illuminate\Support\Str::limit($thread['last']->body, 80) : '' }}</p>
@if ($thread['unread'] > 0)<p class="eyebrow">{{ $thread['unread'] }} unread</p>@endif
<a class="button button-outline" href="{{ route('messages.show', $thread['user']) }}">Open chat</a>
</article>
@empty
<p class="empty-state">No conversations yet. Open a provider profile and click “Message”.</p>
@endforelse
</div>
@endsection
