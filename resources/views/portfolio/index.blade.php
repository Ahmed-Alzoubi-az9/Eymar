@extends('layouts.app')
@section('title', 'Portfolio')
@section('content')
<div class="page-heading"><p class="eyebrow">Showcase</p><h1>Portfolio</h1>
<p class="muted">Each item holds up to 8 photos — previous projects, certifications and work quality.</p></div>
@if (auth()->user()->isServiceProvider())
<section class="form-card">
<form class="stack-form" action="{{ route('portfolio.store') }}" method="post" enctype="multipart/form-data">
@csrf
<label>Title<input name="title" maxlength="160" required></label>
<label>Description<textarea name="description" rows="3" maxlength="3000"></textarea></label>
<label>Photos (1–8)<input type="file" name="images[]" accept="image/*" multiple required></label>
<button class="button button-dark" type="submit">Publish item</button>
</form>
</section>
@endif
<div class="card-grid">
@forelse ($items as $item)
<article class="panel listing-card">
<h3>{{ $item->title }}</h3>
@if ($item->description)<p class="muted">{{ \Illuminate\Support\Str::limit($item->description, 140) }}</p>@endif
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin:8px 0">
@foreach ($item->images as $img)
<img src="{{ \Illuminate\Support\Facades\Storage::url($img->image_path) }}" alt="{{ $item->title }}" style="width:100%;height:70px;object-fit:cover;border-radius:8px">
@endforeach
</div>
@if ($item->provider_id === auth()->id())
<form action="{{ route('portfolio.destroy', $item) }}" method="post">@csrf @method('delete')<button class="button button-outline" type="submit">Delete</button></form>
@endif
</article>
@empty
<p class="empty-state">No portfolio items yet.</p>
@endforelse
</div>
{{ $items->links() }}
@endsection
