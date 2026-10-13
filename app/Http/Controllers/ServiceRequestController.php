<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ServiceQuote;
use App\Models\ServiceRequest as ProjectRequest;
use App\Models\User;
use App\Notifications\MarketplaceNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ServiceRequestController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = ProjectRequest::query()->with(['customer', 'category', 'acceptedProvider'])->withCount('quotes');

        if ($user->role === 'company') {
            $query->where('customer_id', $user->id);
        } elseif ($user->role === 'customer') {
            if ($user->isServiceProvider()) {
                $query->where(function ($requests) use ($user) {
                    $requests->where('customer_id', $user->id)
                        ->orWhere('status', 'open');
                });
            } else {
                $query->where('customer_id', $user->id);
            }
        }

        return view('projects.index', ['projects' => $query->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('projects.create', [
            'categories' => Category::query()->where('type', 'service')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')->where('type', 'service')],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:5000'],
            'location' => ['required', 'string', 'max:160'],
            'budget_min' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2', 'gte:budget_min'],
        ]);

        $project = DB::transaction(function () use ($request, $validated): ProjectRequest {
            $project = $request->user()->customerServiceRequests()->create($validated);
            User::query()
                ->whereHas('serviceProviderProfile', fn ($profile) => $profile->where('is_available', true))
                ->where('status', 'active')
                ->each(fn (User $provider) => $provider->notify(new MarketplaceNotification(
                    'New project request',
                    "{$project->title} is open for quotes in your Eymar marketplace.",
                    route('projects.show', $project),
                )));

            return $project;
        });

        return redirect()->route('projects.show', $project)->with('success', __('messages.project_created'));
    }

    public function show(Request $request, ProjectRequest $project): View
    {
        $user = $request->user();
        abort_unless(
            ($project->status === 'open' && $user->isServiceProvider())
                || $project->customer_id === $user->id
                || $project->accepted_provider_id === $user->id
                || $user->role === 'admin',
            404,
        );

        $project->load(['customer', 'category', 'acceptedProvider', 'review', 'bookings.provider', 'bookings.customer']);
        $quotes = $project->quotes()->with('provider')->latest()->get();

        return view('projects.show', compact('project', 'quotes'));
    }

    public function quote(Request $request, ProjectRequest $project): RedirectResponse
    {
        abort_unless($request->user()->isServiceProvider(), 403);
        abort_unless($project->status === 'open' && $project->customer_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:3000'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99', 'decimal:0,2'],
            'estimated_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
        ]);

        DB::transaction(function () use ($request, $project, $validated): void {
            $lockedProject = ProjectRequest::query()->whereKey($project->id)->lockForUpdate()->firstOrFail();
            if (
                $lockedProject->status !== 'open'
                || $lockedProject->customer_id === $request->user()->id
                || $lockedProject->quotes()->where('provider_id', $request->user()->id)->exists()
            ) {
                throw ValidationException::withMessages([
                    'message' => __('messages.already_quoted'),
                ]);
            }

            $lockedProject->quotes()->create([
                ...$validated,
                'provider_id' => $request->user()->id,
            ]);
            $providerName = $request->user()->business_name ?: $request->user()->name;
            $lockedProject->customer->notify(new MarketplaceNotification(
                'New project quote',
                "{$providerName} sent a quote for {$lockedProject->title}.",
                route('projects.show', $lockedProject),
            ));
        });

        return back()->with('success', __('messages.quote_sent'));
    }

    public function acceptQuote(Request $request, ProjectRequest $project, ServiceQuote $quote): RedirectResponse
    {
        abort_unless($project->customer_id === $request->user()->id, 404);
        abort_unless($quote->service_request_id === $project->id, 404);

        DB::transaction(function () use ($project, $quote): void {
            $lockedProject = ProjectRequest::query()->whereKey($project->id)->lockForUpdate()->firstOrFail();

            if ($lockedProject->status !== 'open' || $quote->status !== 'pending') {
                throw ValidationException::withMessages([
                    'quote' => __('messages.project_no_longer_open'),
                ]);
            }

            $lockedProject->quotes()
                ->whereKeyNot($quote->id)
                ->where('status', 'pending')
                ->update(['status' => 'rejected']);
            $quote->update(['status' => 'accepted']);
            $lockedProject->update([
                'accepted_provider_id' => $quote->provider_id,
                'status' => 'awarded',
            ]);
            $quote->provider->notify(new MarketplaceNotification(
                'Your quote was accepted',
                "The customer accepted your quote for {$lockedProject->title}.",
                route('projects.show', $lockedProject),
            ));
        });

        return back()->with('success', __('messages.quote_accepted'));
    }

    public function updateStatus(Request $request, ProjectRequest $project): RedirectResponse
    {
        $user = $request->user();
        $status = $request->validate(['status' => ['required', 'in:in_progress,completed,cancelled']])['status'];

        if ($status === 'in_progress') {
            abort_unless($project->accepted_provider_id === $user->id && $project->status === 'awarded', 403);
        } elseif ($status === 'completed') {
            abort_unless($project->customer_id === $user->id && $project->status === 'in_progress', 403);
        } else {
            abort_unless($project->customer_id === $user->id && $project->status === 'open', 403);
        }

        DB::transaction(function () use ($project, $status, $user): void {
            $lockedProject = ProjectRequest::query()->whereKey($project->id)->lockForUpdate()->firstOrFail();
            if ($lockedProject->status !== $project->status) {
                throw ValidationException::withMessages([
                    'status' => __('messages.project_no_longer_open'),
                ]);
            }

            $pendingQuotes = collect();
            if ($status === 'cancelled') {
                $pendingQuotes = $lockedProject->quotes()
                    ->where('status', 'pending')
                    ->with('provider')
                    ->get();
                $lockedProject->quotes()
                    ->where('status', 'pending')
                    ->update(['status' => 'rejected']);
            }

            $lockedProject->update(['status' => $status]);
            $recipient = $user->id === $lockedProject->customer_id
                ? $lockedProject->acceptedProvider
                : $lockedProject->customer;
            $recipient?->notify(new MarketplaceNotification(
                'Project status updated',
                "The project \"{$lockedProject->title}\" is now {$status}.",
                route('projects.show', $lockedProject),
            ));

            foreach ($pendingQuotes as $quote) {
                $quote->provider->notify(new MarketplaceNotification(
                    'Project request cancelled',
                    "The customer cancelled {$lockedProject->title}. Your quote is no longer under consideration.",
                    route('projects.index'),
                ));
            }
        });

        return back()->with('success', __('messages.project_status_updated'));
    }
}
