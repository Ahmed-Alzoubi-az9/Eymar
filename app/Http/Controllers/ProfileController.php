<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()->loadMissing('serviceProviderProfile')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
            'username' => strtolower(trim((string) $request->input('username'))),
            'phone' => preg_replace('/[\s().-]+/', '', trim((string) $request->input('phone'))),
        ]);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'username' => ['required', 'alpha_dash:ascii', 'min:3', 'max:32', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'location' => ['nullable', 'string', 'max:160'],
            'address' => ['nullable', 'string', 'max:2000'],
            'age' => ['nullable', 'integer', 'min:13', 'max:120'],
            'profile_image' => ['nullable', 'image', 'max:4096'],
            'business_name' => [Rule::requiredIf($user->role === 'company'), 'nullable', 'string', 'max:160'],
            'company_owner' => [Rule::requiredIf($user->role === 'company'), 'nullable', 'string', 'max:120'],
            'established_date' => ['nullable', 'date', 'before_or_equal:today'],
            'company_logo' => ['nullable', 'image', 'max:4096'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'provider_type' => ['nullable', Rule::in(['contractor', 'craftsman'])],
            'experience_years' => ['required_with:provider_type', 'nullable', 'integer', 'min:0', 'max:70'],
            'previous_work' => ['nullable', 'string', 'max:5000'],
            'pricing_basis' => ['required_with:provider_type', 'nullable', Rule::in(['hourly', 'unit'])],
            'service_rate' => ['required_with:provider_type', 'nullable', 'numeric', 'min:0.01', 'max:99999999.99', 'decimal:0,2'],
            'is_available' => ['sometimes', 'boolean'],
        ]);

        abort_unless($user->role === 'customer' || ! $request->filled('provider_type'), 403);

        $profileImage = $request->file('profile_image');
        $companyLogo = $request->file('company_logo');
        unset($validated['profile_image'], $validated['company_logo']);
        $providerType = $validated['provider_type'] ?? null;
        $providerFields = [
            'provider_type' => $providerType,
            'experience_years' => $validated['experience_years'] ?? null,
            'previous_work' => $validated['previous_work'] ?? null,
            'pricing_basis' => $validated['pricing_basis'] ?? null,
            'service_rate' => $validated['service_rate'] ?? null,
            'is_available' => $request->boolean('is_available'),
        ];
        $oldProfileImage = $user->profile_image_path;
        $oldCompanyLogo = $user->company_logo_path;
        unset(
            $validated['provider_type'],
            $validated['experience_years'],
            $validated['previous_work'],
            $validated['pricing_basis'],
            $validated['service_rate'],
            $validated['is_available'],
        );
        if ($user->role !== 'customer') {
            unset($validated['age']);
        }
        if ($user->role !== 'company') {
            unset($validated['business_name'], $validated['company_owner'], $validated['established_date'], $validated['bio']);
        }

        if ($profileImage) {
            $validated['profile_image_path'] = $this->storeImage($profileImage);
        }
        if ($companyLogo && $user->role === 'company') {
            $validated['company_logo_path'] = $this->storeImage($companyLogo, 'companies');
        }

        $user->update($validated);

        if ($providerType) {
            $user->serviceProviderProfile()->updateOrCreate([], $providerFields);
        } elseif ($request->exists('provider_type')) {
            $user->serviceProviderProfile()->delete();
        }

        if ($profileImage && $oldProfileImage) {
            Storage::disk('public')->delete($oldProfileImage);
        }
        if ($companyLogo && $oldCompanyLogo) {
            Storage::disk('public')->delete($oldCompanyLogo);
        }

        return back()->with('success', __('messages.profile_updated'));
    }

    private function storeImage(UploadedFile $image, string $directory = 'profiles'): string
    {
        $path = $image->store($directory, 'public');
        if (! is_string($path)) {
            throw new \RuntimeException('The profile image could not be stored.');
        }

        return $path;
    }
}
