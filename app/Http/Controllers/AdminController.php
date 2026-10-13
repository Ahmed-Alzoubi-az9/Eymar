<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Models\Order;
use App\Models\Product;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'usersCount' => User::query()->where('role', '!=', 'admin')->count(),
            'pendingProductsCount' => Product::query()->where('is_active', false)->where('stock', '>', 0)->count(),
            'ordersCount' => Order::query()->count(),
            'openProjectsCount' => ServiceRequest::query()->where('status', 'open')->count(),
        ]);
    }

    public function users(Request $request): View
    {
        return view('admin.users', [
            'users' => User::query()
                ->where('role', '!=', 'admin')
                ->when($request->filled('q'), function ($query) use ($request) {
                    $term = $request->string('q')->trim()->toString();
                    $query->where(fn ($query) => $query
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('business_name', 'like', "%{$term}%"));
                })
                ->latest()
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function updateUserStatus(Request $request, User $user): RedirectResponse
    {
        abort_if($user->role === 'admin' || $user->id === $request->user()->id, 403);
        $status = $request->validate(['status' => ['required', 'in:active,suspended']])['status'];
        $user->update(['status' => $status]);
        if ($status === 'suspended') {
            $user->products()->update(['is_active' => false]);
        }

        return back()->with('success', __('messages.user_status_updated'));
    }

    public function products(): View
    {
        return view('admin.products', [
            'products' => Product::query()->with(['seller', 'category'])->latest()->paginate(20),
        ]);
    }

    public function updateProductStatus(Request $request, Product $product): RedirectResponse
    {
        $active = $request->boolean('is_active');
        abort_if($active && ($product->stock < 1 || $product->seller->status !== 'active'), 422);
        $product->update(['is_active' => $active]);

        return back()->with('success', __('messages.product_status_updated'));
    }

    public function orders(): View
    {
        return view('admin.orders', [
            'orders' => Order::query()->with(['buyer', 'seller', 'items'])->latest()->paginate(20),
        ]);
    }

    public function posts(): View
    {
        return view('admin.posts', [
            'posts' => CommunityPost::query()->with(['user'])->latest()->paginate(20),
        ]);
    }

    public function destroyPost(CommunityPost $post): RedirectResponse
    {
        $post->delete();

        return back()->with('success', 'Post removed by admin.');
    }

    public function projects(): View
    {
        return view('admin.projects', [
            'projects' => ServiceRequest::query()->with(['customer', 'acceptedProvider'])->latest()->paginate(20),
        ]);
    }

    public function destroyProject(ServiceRequest $project): RedirectResponse
    {
        $project->delete();

        return back()->with('success', 'Project removed by admin.');
    }
}
