@extends('layouts.app')
@section('title', 'Moderate projects')
@section('content')
<div class="page-heading"><p class="eyebrow">Admin</p><h1>Projects</h1></div>
<div class="table-wrap"><table><thead><tr><th>ID</th><th>Title</th><th>Customer</th><th>Status</th><th></th></tr></thead><tbody>
@forelse ($projects as $project)
<tr><td>{{ $project->id }}</td><td><a href="{{ route('projects.show', $project) }}">{{ $project->title }}</a></td><td>{{ $project->customer?->name }}</td><td>{{ $project->status }}</td>
<td><form action="{{ route('admin.projects.destroy', $project) }}" method="post" onsubmit="return confirm('Remove this project?')">@csrf @method('delete')<button class="button button-outline" type="submit">Remove</button></form></td></tr>
@empty<tr><td colspan="5">No projects.</td></tr>@endforelse
</tbody></table></div>
{{ $projects->links() }}
@endsection
