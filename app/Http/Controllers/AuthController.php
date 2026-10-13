<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $email = $request->input('email');
        if (is_string($email)) {
            $request->merge(['email' => strtolower(trim($email))]);
        }
        $username = $request->input('username');
        if (is_string($username)) {
            $request->merge(['username' => strtolower(trim($username))]);
        }
        $request->merge(['phone' => $this->normalizePhone($request->input('phone'))]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'username' => ['required', 'alpha_dash:ascii', 'min:3', 'max:32', 'unique:users,username'],
            'role' => ['required', 'in:customer,company'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'location' => ['nullable', 'string', 'max:160'],
            'address' => ['nullable', 'string', 'max:2000'],
            'age' => ['nullable', 'integer', 'min:13', 'max:120'],
            'profile_image' => ['nullable', 'image', 'max:4096'],
            'business_name' => ['required_if:role,company', 'nullable', 'string', 'max:160'],
            'company_owner' => ['required_if:role,company', 'nullable', 'string', 'max:120'],
            'established_date' => ['nullable', 'date', 'before_or_equal:today'],
            'company_logo' => ['nullable', 'image', 'max:4096'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $validated['profile_image_path'] = $this->storeImage($validated['profile_image'] ?? null);
        unset($validated['profile_image']);
        if ($validated['role'] === 'company') {
            $validated['company_logo_path'] = $this->storeImage($validated['company_logo'] ?? null, 'companies');
        } else {
            unset($validated['business_name'], $validated['company_owner'], $validated['established_date']);
        }
        unset($validated['company_logo']);

        $user = User::create($validated);
        $user->sendEmailVerificationNotification();
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', __('messages.account_created'));
    }

    public function login(): View
    {
        return view('auth.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $email = $request->input('email');
        if (is_string($email)) {
            $request->merge(['email' => strtolower(trim($email))]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = strtolower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => __('messages.too_many_login_attempts', [
                    'seconds' => RateLimiter::availableIn($key),
                ]),
            ]);
        }

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'status' => 'active',
        ], $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => __('messages.invalid_credentials'),
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        $destination = match ($request->user()->role) {
            'company' => route('products.index'),
            'admin' => route('admin.users.index'),
            default => route('home'),
        };

        return redirect()->intended($destination);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function verifyNotice(): View|RedirectResponse
    {
        return auth()->user()->hasVerifiedEmail()
            ? redirect()->route('dashboard')
            : view('auth.verify-email');
    }

    public function verify(Request $request): RedirectResponse
    {
        if (! hash_equals((string) $request->route('id'), (string) $request->user()->getKey())) {
            abort(403);
        }
        if (! hash_equals((string) $request->route('hash'), sha1((string) $request->user()->getEmailForVerification()))) {
            abort(403);
        }
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }
        $request->user()->markEmailAsVerified();

        return redirect()->route('dashboard')->with('success', 'Email verified. Welcome to Eymar!');
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }
        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent. Check your inbox.');
    }

    private function normalizePhone(mixed $phone): mixed
    {
        return is_string($phone) ? preg_replace('/[\s().-]+/', '', trim($phone)) : $phone;
    }

    private function storeImage(?UploadedFile $image, string $directory = 'profiles'): ?string
    {
        if (! $image) {
            return null;
        }

        $path = $image->store($directory, 'public');
        if (! is_string($path)) {
            throw new \RuntimeException('The profile image could not be stored.');
        }

        return $path;
    }
}
