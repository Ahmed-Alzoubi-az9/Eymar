<?php

namespace App\Http\Controllers;

use App\Models\PortfolioItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(Request $request): View
    {
        $items = PortfolioItem::query()->with('images')
            ->where('provider_id', $request->user()->id)->latest()->paginate(12);

        return view('portfolio.index', compact('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isServiceProvider(), 403);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:3000'],
            'images' => ['required', 'array', 'min:1', 'max:8'],
            'images.*' => ['image', 'max:4096'],
        ]);

        $item = $request->user()->portfolioItems()->create([
            'title' => $validated['title'], 'description' => $validated['description'] ?? null,
        ]);
        foreach ($request->file('images', []) as $i => $image) {
            $item->images()->create(['image_path' => $image->store('portfolio', 'public'), 'sort_order' => $i]);
        }

        return back()->with('success', 'Portfolio item published with '.count($request->file('images', [])).' photos.');
    }

    public function destroy(Request $request, PortfolioItem $item): RedirectResponse
    {
        abort_unless($item->provider_id === $request->user()->id, 403);
        foreach ($item->images as $img) {
            Storage::disk('public')->delete($img->image_path);
        }
        $item->delete();

        return back()->with('success', 'Portfolio item deleted.');
    }
}
