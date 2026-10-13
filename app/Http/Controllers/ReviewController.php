<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use App\Models\ServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function storeForOrder(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user();
        abort_unless($order->buyer_id === $user->id, 403);
        abort_unless($order->status === 'delivered', 422);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        if (Review::where('order_id', $order->id)->where('reviewer_id', $user->id)->exists()) {
            throw ValidationException::withMessages(['rating' => 'You already reviewed this order.']);
        }

        Review::create([
            'order_id' => $order->id,
            'reviewer_id' => $user->id,
            'reviewee_id' => $order->seller_id,
            ...$validated,
        ]);

        return back()->with('success', 'Thank you! Your review was submitted.');
    }

    public function storeForProject(Request $request, ServiceRequest $project): RedirectResponse
    {
        $user = $request->user();
        abort_unless($project->customer_id === $user->id, 403);
        abort_unless($project->status === 'completed' && $project->accepted_provider_id, 422);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        if (Review::where('service_request_id', $project->id)->where('reviewer_id', $user->id)->exists()) {
            throw ValidationException::withMessages(['rating' => 'You already reviewed this project.']);
        }

        Review::create([
            'service_request_id' => $project->id,
            'reviewer_id' => $user->id,
            'reviewee_id' => $project->accepted_provider_id,
            ...$validated,
        ]);

        return back()->with('success', 'Thank you! Your review was submitted.');
    }
}
