@extends('layouts.app')

@section('title', __('messages.projects'))

@section('content')
    <div class="page-heading page-heading-row">
        <div><p class="eyebrow">{{ __('messages.brand') }}</p><h1>{{ __('messages.projects') }}</h1></div>
        @if (auth()->user()->role === 'customer')
            <a class="button button-dark" href="{{ route('projects.create') }}">{{ __('messages.post_project') }}</a>
        @endif
    </div>
    <div class="card-grid">
        @forelse ($projects as $project)
            <article class="panel listing-card">
                <p class="eyebrow">{{ $project->category->display_name }} · {{ __('messages.'.strtolower($project->status)) }}</p>
                <h2><a href="{{ route('projects.show', $project) }}">{{ $project->title }}</a></h2>
                <p>{{ $project->location }}</p>
                <p class="muted">{{ \Illuminate\Support\Str::limit($project->description, 170) }}</p>
                <p>{{ __('messages.quotes') }}: {{ $project->quotes_count }}</p>
                <a class="button button-outline" href="{{ route('projects.show', $project) }}">{{ __('messages.view_profile') }}</a>
            </article>
        @empty
            <p class="empty-state">{{ __('messages.no_projects') }}</p>
        @endforelse
    </div>
    {{ $projects->links() }}
@endsection
