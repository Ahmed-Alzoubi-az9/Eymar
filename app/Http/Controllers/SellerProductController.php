<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SellerProductController extends Controller
{
    public function index(Request $request): View
    {
        return view('seller.products.index', [
            'products' => $request->user()->products()->with('category')->latest()->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('seller.products.form', [
            'product' => new Product,
            'categories' => Category::query()->where('type', 'product')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);
        $image = $validated['image'] ?? null;
        unset($validated['image']);
        if ($image instanceof UploadedFile) {
            $validated['image_path'] = $this->storeImage($image);
        }
        $validated = $this->normalizeProductDetails($validated);

        $request->user()->products()->create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['name']),
            'is_active' => false,
        ]);

        return redirect()->route('seller.products.index')->with('success', __('messages.product_submitted'));
    }

    public function edit(Request $request, Product $product): View
    {
        $this->authorizeOwner($request, $product);

        return view('seller.products.form', [
            'product' => $product,
            'categories' => Category::query()->where('type', 'product')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeOwner($request, $product);
        $validated = $this->validateProduct($request);
        $image = $validated['image'] ?? null;
        unset($validated['image']);
        if ($image instanceof UploadedFile) {
            $validated['image_path'] = $this->storeImage($image);
        }
        $validated = $this->normalizeProductDetails($validated);

        $product->update([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['name'], $product->id),
            'is_active' => false,
        ]);

        return redirect()->route('seller.products.index')->with('success', __('messages.product_resubmitted'));
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeOwner($request, $product);
        $product->update(['is_active' => false, 'stock' => 0]);

        return redirect()->route('seller.products.index')->with('success', __('messages.product_unlisted'));
    }

    private function validateProduct(Request $request): array
    {
        $driverPhone = $request->input('driver_phone');
        if (is_string($driverPhone)) {
            $request->merge([
                'driver_phone' => preg_replace('/[\s().-]+/', '', trim($driverPhone)),
            ]);
        }

        return $request->validate([
            'product_type' => ['required', Rule::in(['material', 'equipment', 'machinery'])],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('type', 'product'),
            ],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999999.99', 'decimal:0,2'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'image' => ['nullable', 'image', 'max:4096'],
            'manufacturing_company' => ['required', 'string', 'max:160'],
            'manufacturing_country' => ['required', 'string', 'max:120'],
            'production_date' => ['required_if:product_type,material,machinery', 'nullable', 'date', 'before_or_equal:today'],
            'expiry_date' => ['required_if:product_type,material,machinery', 'nullable', 'date', 'after_or_equal:production_date'],
            'storage_info' => ['nullable', 'string', 'max:3000'],
            'risk_info' => ['nullable', 'string', 'max:3000'],
            'usage_info' => ['nullable', 'string', 'max:3000'],
            'driver_name' => ['nullable', 'required_if:product_type,machinery', 'string', 'max:120'],
            'driver_phone' => ['nullable', 'required_if:product_type,machinery', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'is_rentable' => ['sometimes', 'boolean'],
            'rental_rate_per_day' => ['required_if:is_rentable,1', 'nullable', 'numeric', 'min:0.01', 'max:99999999.99', 'decimal:0,2'],
        ]);
    }

    private function normalizeProductDetails(array $validated): array
    {
        if ($validated['product_type'] === 'equipment') {
            $validated['production_date'] = null;
            $validated['expiry_date'] = null;
        }

        if ($validated['product_type'] !== 'machinery') {
            $validated['driver_name'] = null;
            $validated['driver_phone'] = null;
        }

        if ($validated['product_type'] === 'material') {
            $validated['is_rentable'] = false;
        } else {
            $validated['is_rentable'] = (bool) ($validated['is_rentable'] ?? false);
        }
        if (! $validated['is_rentable']) {
            $validated['rental_rate_per_day'] = null;
        }

        return $validated;
    }

    private function storeImage(UploadedFile $image): string
    {
        $path = $image->store('products', 'public');
        if (! is_string($path)) {
            throw new \RuntimeException('The product image could not be stored.');
        }

        return $path;
    }

    private function uniqueSlug(string $name, ?int $exceptId = null): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $suffix = 2;

        while (Product::query()->where('slug', $slug)->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function authorizeOwner(Request $request, Product $product): void
    {
        abort_unless($product->seller_id === $request->user()->id, 403);
    }
}
