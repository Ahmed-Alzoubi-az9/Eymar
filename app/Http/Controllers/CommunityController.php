<?php

namespace App\Http\Controllers;

use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Notifications\MarketplaceNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function index(Request $request): View
    {
        $posts = CommunityPost::query()
            ->with(['user', 'comments.user'])
            ->withCount(['comments', 'likes'])
            ->latest()
            ->paginate(10);

        $likedPostIds = $request->user()
            ? DB::table('community_likes')
                ->where('user_id', $request->user()->id)
                ->whereIn('post_id', $posts->pluck('id'))
                ->pluck('post_id')
                ->all()
            : [];

        return view('community.index', compact('posts', 'likedPostIds'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $post = $request->user()->communityPosts()->create($validated);

        return redirect()->route('community.index')->with('success', __('messages.post_created'))->withFragment('post-'.$post->id);
    }

    public function comment(Request $request, CommunityPost $post): RedirectResponse
    {
        $validated = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);
        if ($post->user_id !== $request->user()->id) {
            $post->user->notify(new MarketplaceNotification(
                'New community comment',
                "{$request->user()->name} commented on your post \"{$post->title}\".",
                route('community.index').'#post-'.$post->id,
            ));
        }

        return back()->with('success', __('messages.comment_added'));
    }

    public function toggleLike(Request $request, CommunityPost $post): RedirectResponse
    {
        DB::transaction(function () use ($request, $post): void {
            CommunityPost::query()->whereKey($post->id)->lockForUpdate()->firstOrFail();
            $like = DB::table('community_likes')
                ->where('post_id', $post->id)
                ->where('user_id', $request->user()->id)
                ->first();

            if ($like) {
                DB::table('community_likes')->where('id', $like->id)->delete();
            } else {
                DB::table('community_likes')->insert([
                    'post_id' => $post->id,
                    'user_id' => $request->user()->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return back();
    }

    public function destroy(Request $request, CommunityPost $post): RedirectResponse
    {
        abort_unless($post->user_id === $request->user()->id || $request->user()->role === 'admin', 403);
        $post->delete();

        return back()->with('success', __('messages.post_deleted'));
    }

    public function destroyComment(Request $request, CommunityComment $comment): RedirectResponse
    {
        abort_unless($comment->user_id === $request->user()->id || $comment->post()->value('user_id') === $request->user()->id || $request->user()->role === 'admin', 403);
        $comment->delete();

        return back()->with('success', __('messages.comment_deleted'));
    }
}
