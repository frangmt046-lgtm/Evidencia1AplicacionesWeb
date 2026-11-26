<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Employee login. There is NO registration route: customers cannot create
 * accounts, only the Administrator registers employees.
 */
class LoginController extends Controller
{
    /**
     * GET /login
     */
    public function show()
    {
        return view('auth.login');
    }

    /**
     * POST /login
     * "login" can be the username or the email.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string|max:255',
            'password' => 'required|string',
        ], [
            'login.required' => 'Enter your username or email.',
            'password.required' => 'Enter your password.',
        ]);

        $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::with('role')->where($field, $credentials['login'])->first();

        // Same message for "user does not exist" and "wrong password",
        // so nobody can find out which usernames exist.
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['login' => 'Incorrect username/email or password.'])
                ->onlyInput('login');
        }

        if (! $user->is_active) {
            return back()
                ->withErrors(['login' => 'Your account is deactivated. Contact the Administrator.'])
                ->onlyInput('login');
        }

        if (! $user->role) {
            return back()
                ->withErrors(['login' => 'Your account has no role assigned. Contact the Administrator.'])
                ->onlyInput('login');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate(); // protects against session fixation

        return redirect()->intended(route('dashboard'));
    }

    /**
     * POST /logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have logged out.');
    }
}
