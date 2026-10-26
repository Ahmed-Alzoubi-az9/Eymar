@extends('layouts.app')

@section('title', __('messages.community'))

@section('content')
    <div class="page-heading"><h1>{{ __('messages.community') }}</h1></div>
    @auth
        <section class="form-card community-compose">
            <h2>{{ __('messages.create_post') }}</h2>
            <form class="stack-form" action="{{ route('community.store') }}" method="post">
                @csrf
                <label>{{ __('messages.post_title') }}<input name="title" maxlength="160" required></label>
                <label>{{ __('messages.post_body') }}<textarea name="body" rows="4" maxlength="5000" required></textarea></label>
                <button class="button button-dark" type="submit">{{ __('messages.publish') }}</button>
            </form>
        </section>
    @else
        <section class="community-signin">
            <strong>{{ __('messages.create_post') }}</strong>
            <a class="button button-dark" href="{{ route('login') }}">{{ __('messages.login') }}</a>
        </section>
    @endauth

    <div class="feed">
        @forelse ($posts as $post)
            <article class="panel feed-post" id="post-{{ $post->id }}">
                <div class="page-heading-row">
                    <div><h2>{{ $post->title }}</h2><p class="muted">{{ $post->user->name }} · {{ $post->created_at->diffForHumans() }}</p></div>
                    @auth
                        @if (auth()->id() === $post->user_id || auth()->user()->role === 'admin')
                            <form action="{{ route('community.destroy', $post) }}" method="post">
                                @csrf @method('delete')<button class="text-button" type="submit">{{ __('messages.delete') }}</button>
                            </form>
                        @endif
                    @endauth
                </div>
                <p class="post-body">{{ $post->body }}</p>
                <div class="feed-actions">
                    @auth
                        <form action="{{ route('community.likes.toggle', $post) }}" method="post">
                            @csrf
                            <button class="text-button" type="submit">{{ in_array($post->id, $likedPostIds, true) ? __('messages.unlike') : __('messages.like') }} ({{ $post->likes_count }})</button>
                        </form>
                    @else
                        <span>{{ __('messages.like') }} ({{ $post->likes_count }})</span>
                    @endauth
                    <span>{{ __('messages.comment') }} ({{ $post->comments_count }})</span>
                </div>
                <div class="comment-list">
                    @foreach ($post->comments as $comment)
                        <div class="comment-item">
                            <p><strong>{{ $comment->user->name }}</strong> · {{ $comment->body }}</p>
                            @auth
                                @if (auth()->id() === $comment->user_id || auth()->id() === $post->user_id || auth()->user()->role === 'admin')
                                    <form action="{{ route('community.comments.destroy', $comment) }}" method="post">
                                        @csrf @method('delete')<button class="text-button" type="submit">{{ __('messages.delete') }}</button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                    @endforeach
                    @auth
                        <form class="inline-form comment-form" action="{{ route('community.comments.store', $post) }}" method="post">
                            @csrf
                            <input name="body" maxlength="2000" placeholder="{{ __('messages.write_comment') }}" required>
                            <button class="button button-outline" type="submit">{{ __('messages.comment') }}</button>
                        </form>
                    @endauth
                </div>
            </article>
        @empty
            <p class="empty-state">{{ __('messages.no_posts') }}</p>
        @endforelse
    </div>
    {{ $posts->links() }}
@endsection
