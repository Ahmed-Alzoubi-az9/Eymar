@extends('layouts.app')
@section('title', 'Moderate posts')
@section('content')
<div class="page-heading"><p class="eyebrow">Admin</p><h1>Community posts</h1></div>
<div class="table-wrap"><table><thead><tr><th>ID</th><th>Author</th><th>Content</th><th>Date</th><th></th></tr></thead><tbody>
@forelse ($posts as $post)
<tr><td>{{ $post->id }}</td><td>{{ $post->user?->name }}</td><td>{{ \Illuminate\Support\Str::limit($post->body ?? $post->content ?? '', 120) }}</td><td>{{ $post->created_at }}</td>
<td><form action="{{ route('admin.posts.destroy', $post) }}" method="post" onsubmit="return confirm('Remove this post?')">@csrf @method('delete')<button class="button button-outline" type="submit">Remove</button></form></td></tr>
@empty<tr><td colspan="5">No posts.</td></tr>@endforelse
</tbody></table></div>
{{ $posts->links() }}
@endsection
