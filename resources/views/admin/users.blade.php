@extends('layouts.app')

@section('title', __('messages.manage_users'))

@section('content')
    <div class="page-heading"><p class="eyebrow">{{ __('messages.admin') }}</p><h1>{{ __('messages.manage_users') }}</h1></div>
    <form class="search-form" method="get">
        <input name="q" value="{{ request('q') }}" placeholder="{{ __('messages.search') }}">
        <button class="button button-dark" type="submit">{{ __('messages.search') }}</button>
    </form>
    <div class="table-wrap">
        <table>
            <thead><tr><th>{{ __('messages.name') }}</th><th>{{ __('messages.email') }}</th><th>{{ __('messages.account_type') }}</th><th>{{ __('messages.location') }}</th><th>{{ __('messages.user_status') }}</th><th>{{ __('messages.actions') }}</th></tr></thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->business_name ?: $user->name }}</td><td>{{ $user->email }}</td>
                        <td>{{ __("messages.{$user->role}") }}</td><td>{{ $user->location }}</td><td>{{ __("messages.{$user->status}") }}</td>
                        <td>
                            <form action="{{ route('admin.users.status', $user) }}" method="post">
                                @csrf @method('patch')
                                <input type="hidden" name="status" value="{{ $user->status === 'active' ? 'suspended' : 'active' }}">
                                <button class="text-button" type="submit">{{ $user->status === 'active' ? __('messages.suspended') : __('messages.active') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">{{ __('messages.manage_users') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
@endsection
