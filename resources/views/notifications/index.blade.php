@extends('layouts.app')

@section('title', __('messages.notifications'))

@section('content')
    <div class="page-heading page-heading-row">
        <div><p class="eyebrow">{{ __('messages.dashboard') }}</p><h1>{{ __('messages.notifications') }}</h1></div>
        @if (auth()->user()->unreadNotifications()->exists())
            <form action="{{ route('notifications.read-all') }}" method="post">
                @csrf @method('patch')<button class="button button-outline" type="submit">{{ __('messages.mark_all_read') }}</button>
            </form>
        @endif
    </div>
    <div class="feed">
        @forelse ($notifications as $notification)
            <article class="panel notification-card {{ $notification->read_at ? '' : 'notification-unread' }}">
                <h2>{{ $notification->data['title'] ?? __('messages.notifications') }}</h2>
                <p>{{ $notification->data['message'] ?? '' }}</p>
                <p class="muted">{{ $notification->created_at->diffForHumans() }}</p>
                @if (! $notification->read_at)
                    <form action="{{ route('notifications.read', $notification->id) }}" method="post">
                        @csrf @method('patch')<button class="text-button" type="submit">{{ __('messages.unread') }}</button>
                    </form>
                @endif
            </article>
        @empty
            <p class="empty-state">{{ __('messages.no_notifications') }}</p>
        @endforelse
    </div>
    {{ $notifications->links() }}
@endsection
