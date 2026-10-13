<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $counterparts = Message::query()
            ->where(fn ($q) => $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id))
            ->latest()->get()
            ->map(fn (Message $m) => $m->sender_id === $user->id ? $m->recipient_id : $m->sender_id)
            ->unique()->values();

        $users = User::query()->whereKey($counterparts)->get()->keyBy('id');
        $threads = $counterparts->map(function ($id) use ($users, $user) {
            $last = Message::query()
                ->where(function ($q) use ($user, $id) {
                    $q->where('sender_id', $user->id)->where('recipient_id', $id);
                })
                ->orWhere(function ($q) use ($user, $id) {
                    $q->where('sender_id', $id)->where('recipient_id', $user->id);
                })
                ->latest()->first();

            return [
                'user' => $users->get($id),
                'unread' => Message::where('sender_id', $id)->where('recipient_id', $user->id)->whereNull('read_at')->count(),
                'last' => $last,
            ];
        })->filter(fn ($t) => $t['user']);

        return view('messages.index', compact('threads'));
    }

    public function show(Request $request, User $user): View
    {
        abort_if($user->id === $request->user()->id, 404);
        $me = $request->user()->id;

        Message::query()->where('sender_id', $user->id)->where('recipient_id', $me)
            ->whereNull('read_at')->update(['read_at' => now()]);

        $messages = Message::query()->with(['sender', 'recipient'])
            ->where(fn ($q) => $q->where('sender_id', $me)->where('recipient_id', $user->id))
            ->orWhere(fn ($q) => $q->where('sender_id', $user->id)->where('recipient_id', $me))
            ->orderBy('id')->paginate(50);

        return view('messages.show', ['other' => $user, 'messages' => $messages]);
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id, 403);
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'service_request_id' => ['nullable', 'exists:service_requests,id'],
            'order_id' => ['nullable', 'exists:orders,id'],
        ]);

        Message::create([
            ...$validated, 'sender_id' => $request->user()->id, 'recipient_id' => $user->id,
        ]);

        return back()->with('success', 'Message sent.');
    }

    // Polled every 3s by the thread page for real-time feel (no websockets needed on XAMPP).
    public function poll(Request $request, User $user): JsonResponse
    {
        $me = $request->user()->id;
        $after = (int) $request->query('after', 0);
        $messages = Message::query()
            ->where('id', '>', $after)
            ->where(fn ($q) => $q->where(fn ($q) => $q->where('sender_id', $me)->where('recipient_id', $user->id))
                ->orWhere(fn ($q) => $q->where('sender_id', $user->id)->where('recipient_id', $me)))
            ->orderBy('id')->limit(50)->get(['id', 'sender_id', 'body', 'created_at']);

        Message::query()->where('sender_id', $user->id)->where('recipient_id', $me)
            ->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['messages' => $messages]);
    }
}
