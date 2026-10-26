@extends('layouts.app')

@section('title', __('messages.post_project'))

@section('content')
    <section class="form-card">
        <div class="page-heading"><p class="eyebrow">{{ __('messages.projects') }}</p><h1>{{ __('messages.new_project') }}</h1></div>
        <form class="stack-form" action="{{ route('projects.store') }}" method="post">
            @csrf
            <label>{{ __('messages.project_title') }}<input name="title" value="{{ old('title') }}" maxlength="160" placeholder="{{ __('messages.placeholder_project_title') }}" required></label>
            <label>{{ __('messages.category') }}
                <select name="category_id" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->display_name }}</option>
                    @endforeach
                </select>
            </label>
            <label>{{ __('messages.description') }}<textarea name="description" rows="6" maxlength="5000" placeholder="{{ __('messages.placeholder_description') }}" required>{{ old('description') }}</textarea></label>
            <label>{{ __('messages.location') }}<input name="location" value="{{ old('location', auth()->user()->location) }}" maxlength="160" placeholder="{{ __('messages.placeholder_location') }}" required></label>
            <div class="form-columns">
                <label>{{ __('messages.budget_min') }}<input name="budget_min" type="number" min="0" step="0.01" value="{{ old('budget_min') }}" placeholder="{{ __('messages.placeholder_budget') }}"></label>
                <label>{{ __('messages.budget_max') }}<input name="budget_max" type="number" min="0" step="0.01" value="{{ old('budget_max') }}" placeholder="{{ __('messages.placeholder_budget') }}"></label>
            </div>
            <button class="button button-dark" type="submit">{{ __('messages.post_project') }}</button>
        </form>
    </section>
@endsection
