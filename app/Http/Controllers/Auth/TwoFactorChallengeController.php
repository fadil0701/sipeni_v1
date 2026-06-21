<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TwoFactorChallengeController extends Controller
{
    public function show(Request $request)
    {
        if (! $request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $userId = $request->session()->get('login.id');
        if (! $userId) {
            return redirect()->route('login');
        }

        /** @var User|null $user */
        $user = User::query()->find($userId);
        if (! $user || ! TwoFactorService::verifyCode($user, $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => ['Kode autentikasi tidak valid.'],
            ]);
        }

        Auth::login($user, (bool) $request->session()->get('login.remember', false));
        $request->session()->forget(['login.id', 'login.remember']);
        $request->session()->regenerate();
        $request->session()->put('two_factor_passed_at', now()->timestamp);

        return redirect()->intended(route('user.dashboard'));
    }
}
