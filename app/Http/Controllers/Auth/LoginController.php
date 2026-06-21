<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\TwoFactorService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('user.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            AuditLogService::log('login_failed', $request, 'users', null, null, [
                'email' => $request->input('email'),
            ]);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        $request->session()->regenerate();

        /** @var User|null $user */
        $user = Auth::user();
        if ($user instanceof User && ! ($user->is_active ?? true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            AuditLogService::log('login_failed', $request, 'users', $user->id, null, [
                'email' => $user->email,
                'reason' => 'inactive_account',
            ]);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        if ($user instanceof User) {
            $user->load('roles.permissions');

            if (TwoFactorService::userNeedsChallenge($user)) {
                $userId = $user->id;
                $remember = $request->filled('remember');
                Auth::logout();
                $request->session()->put('login.id', $userId);
                $request->session()->put('login.remember', $remember);

                return redirect()->route('two-factor.challenge');
            }

            $user->forceFill(['last_login' => now()])->save();
            AuditLogService::log('login', $request, 'users', $user->id, null, [
                'email' => $user->email,
            ]);
        }

        return redirect()->intended(route('user.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
