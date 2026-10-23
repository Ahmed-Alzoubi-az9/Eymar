@extends('layouts.app')
@section('title', 'Chat with '.($other->business_name ?: $other->name))
@section('content')
<div class="page-heading"><p class="eyebrow"><a href="{{ route('messages.index') }}">Messages</a></p>
<h1>{{ $other->business_name ?: $other->name }}</h1><p class="muted">{{ $other->email }} {{ $other->phone ? '· '.$other->phone : '' }}</p></div>
<section class="panel" id="thread" data-poll-url="{{ route('messages.poll', $other) }}" data-user-id="{{ auth()->id() }}" data-last-id="{{ $messages->last()?->id ?? 0 }}">
<div id="message-list" style="display:flex;flex-direction:column;gap:10px;max-height:55vh;overflow:auto">
@foreach ($messages as $m)
<div data-id="{{ $m->id }}" style="align-self:{{ $m->sender_id === auth()->id() ? 'flex-end' : 'flex-start' }};background:{{ $m->sender_id === auth()->id() ? '#111827' : '#f3f4f6' }};color:{{ $m->sender_id === auth()->id() ? '#fff' : '#111' }};padding:8px 12px;border-radius:12px;max-width:75%">{{ $m->body }}<br><small style="opacity:.7">{{ $m->created_at->format('Y-m-d H:i') }}</small></div>
@endforeach
</div>
{{ $messages->links() }}
<form class="stack-form message-form" action="{{ route('messages.store', $other) }}" method="post">
@csrf
<label>Message<textarea name="body" rows="3" maxlength="2000" required placeholder="Write…"></textarea></label>
<button class="button button-dark" type="submit">Send</button>
</form>
<p class="muted"><small>Real-time: this thread checks for new messages every 3 seconds.</small></p>
</section>
<script>
(function(){
  const box = document.getElementById('thread'); if(!box) return;
  const list = document.getElementById('message-list');
  const me = parseInt(box.dataset.userId, 10);
  let lastId = parseInt(box.dataset.lastId || '0', 10);
  setInterval(async () => {
    try {
      const res = await fetch(box.dataset.pollUrl + '?after=' + lastId, {headers:{'X-Requested-With':'XMLHttpRequest'}});
      if(!res.ok) return;
      const data = await res.json();
      (data.messages || []).forEach(m => {
        lastId = Math.max(lastId, m.id);
        const div = document.createElement('div');
        const mine = m.sender_id === me;
        div.dataset.id = m.id;
        div.style.alignSelf = mine ? 'flex-end' : 'flex-start';
        div.style.background = mine ? '#111827' : '#f3f4f6';
        div.style.color = mine ? '#fff' : '#111';
        div.style.padding = '8px 12px'; div.style.borderRadius = '12px'; div.style.maxWidth = '75%';
        div.textContent = m.body;
        list.appendChild(div);
        list.scrollTop = list.scrollHeight;
      });
    } catch(e){}
  }, 3000);
})();
</script>
@endsection
