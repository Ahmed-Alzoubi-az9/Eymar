<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function requestForm(): View
    {
        return view('auth.passwords.email');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $validated = $request->validate(['email' => ['required', 'email', 'max:255']]);
        $status = PasswordBroker::sendResetLink($validated);

        if ($status === PasswordBroker::RESET_LINK_SENT || $status === PasswordBroker::INVALID_USER) {
            return back()->with('status', __('messages.password_reset_if_account_exists'));
        }

        throw ValidationException::withMessages([
            'email' => __('messages.password_reset_throttled'),
        ]);
    }

    public function resetForm(string $token): View
    {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => request()->string('email')->toString(),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $status = PasswordBroker::reset($validated, function ($user, string $password): void {
            $user->forceFill(['password' => $password])->save();
            $user->setRememberToken(str()->random(60));
            $user->save();
        });

        if ($status === PasswordBroker::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', __('messages.password_reset_complete'));
        }

        throw ValidationException::withMessages([
            'email' => __('messages.password_reset_invalid'),
        ]);
    }
}
