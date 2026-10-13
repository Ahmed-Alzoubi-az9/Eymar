<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function professionals(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:160'],
            'provider_type' => ['nullable', Rule::in(['contractor', 'craftsman'])],
            'min_experience' => ['nullable', 'integer', 'min:0', 'max:70'],
            'max_experience' => ['nullable', 'integer', 'min:0', 'max:70', 'gte:min_experience'],
            'min_rate' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'max_rate' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2', 'gte:min_rate'],
            'pricing_basis' => ['nullable', Rule::in(['hourly', 'unit'])],
            'availability' => ['nullable', Rule::in(['available', 'unavailable'])],
        ]);

        $providers = User::query()
            ->where(function ($query) {
                $query->where('role', 'company')
                    ->orWhereHas('serviceProviderProfile');
            })
            ->where('status', 'active')
            ->with('serviceProviderProfile')
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $term = trim($term);
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('business_name', 'like', "%{$term}%")
                        ->orWhere('location', 'like', "%{$term}%")
                        ->orWhereHas('serviceProviderProfile', fn ($profile) => $profile->where('previous_work', 'like', "%{$term}%"));
                });
            })
            ->when($filters['location'] ?? null, fn ($query, string $location) => $query->where('location', 'like', '%'.trim($location).'%'))
            ->when($filters['provider_type'] ?? null, fn ($query, string $type) => $query->whereHas('serviceProviderProfile', fn ($profile) => $profile->where('provider_type', $type)))
            ->when(isset($filters['min_experience']), fn ($query) => $query->whereHas('serviceProviderProfile', fn ($profile) => $profile->where('experience_years', '>=', $filters['min_experience'])))
            ->when(isset($filters['max_experience']), fn ($query) => $query->whereHas('serviceProviderProfile', fn ($profile) => $profile->where('experience_years', '<=', $filters['max_experience'])))
            ->when(isset($filters['min_rate']), fn ($query) => $query->whereHas('serviceProviderProfile', fn ($profile) => $profile->where('service_rate', '>=', $filters['min_rate'])))
            ->when(isset($filters['max_rate']), fn ($query) => $query->whereHas('serviceProviderProfile', fn ($profile) => $profile->where('service_rate', '<=', $filters['max_rate'])))
            ->when($filters['pricing_basis'] ?? null, fn ($query, string $basis) => $query->whereHas('serviceProviderProfile', fn ($profile) => $profile->where('pricing_basis', $basis)))
            ->when(($filters['availability'] ?? null) === 'available', fn ($query) => $query->whereHas('serviceProviderProfile', fn ($profile) => $profile->where('is_available', true)))
            ->when(($filters['availability'] ?? null) === 'unavailable', fn ($query) => $query->whereHas('serviceProviderProfile', fn ($profile) => $profile->where('is_available', false)))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('marketplace.professionals', compact('providers'));
    }

    public function professional(User $provider): View
    {
        abort_unless(
            ($provider->role === 'company' || $provider->serviceProviderProfile()->exists())
                && $provider->status === 'active',
            404,
        );

        return view('marketplace.professional', [
            'provider' => $provider->load([
                'serviceProviderProfile',
                'products' => fn ($query) => $query->where('is_active', true)->latest(),
                'portfolioItems.images',
                'reviewsReceived.reviewer',
                'providerSchedules' => fn ($q) => $q->where('date', '>=', now()->toDateString())->orderBy('date')->orderBy('start_time')->limit(14),
            ]),
            'averageRating' => $provider->averageRating(),
            'reviewsCount' => $provider->reviewsReceived()->count(),
        ]);
    }

    public function products(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => [
                'nullable',
                Rule::exists('categories', 'slug')->where('type', 'product'),
            ],
            'product_type' => ['nullable', Rule::in(['material', 'equipment', 'machinery'])],
            'min_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2', 'gte:min_price'],
            'availability' => ['nullable', Rule::in(['in_stock', 'out_of_stock'])],
            'min_stock' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]);
        $products = Product::query()
            ->with(['seller', 'category'])
            ->where('is_active', true)
            ->whereHas('seller', fn ($seller) => $seller->where('status', 'active'))
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $term = trim($term);
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->when($filters['category'] ?? null, function ($query, string $slug) {
                $query->whereHas('category', fn ($category) => $category->where('slug', $slug));
            })
            ->when($filters['product_type'] ?? null, fn ($query, string $type) => $query->where('product_type', $type))
            ->when(isset($filters['min_price']), fn ($query) => $query->where('price', '>=', $filters['min_price']))
            ->when(isset($filters['max_price']), fn ($query) => $query->where('price', '<=', $filters['max_price']))
            ->when(($filters['availability'] ?? null) === 'in_stock', fn ($query) => $query->where('stock', '>', 0))
            ->when(($filters['availability'] ?? null) === 'out_of_stock', fn ($query) => $query->where('stock', '=', 0))
            ->when(isset($filters['min_stock']), fn ($query) => $query->where('stock', '>=', $filters['min_stock']))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('marketplace.products', [
            'products' => $products,
            'categories' => Category::query()->where('type', 'product')->orderBy('name')->get(),
        ]);
    }

    public function product(Product $product): View
    {
        abort_unless($product->is_active && $product->seller->status === 'active', 404);

        return view('marketplace.product', [
            'product' => $product->load(['seller', 'category']),
        ]);
    }
}
