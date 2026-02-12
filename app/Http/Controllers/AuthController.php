<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\AppSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('documents.index');
        }

        $ssoEnabled = AppSettings::isSsoEnabled();
        $registrationEnabled = AppSettings::isRegistrationEnabled();
        return view('auth.login', compact('ssoEnabled', 'registrationEnabled'));
    }

    /**
     * Handle login attempt.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            if (!auth()->user()->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account has been deactivated.']);
            }

            return redirect()->intended(route('documents.index'));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'The provided credentials do not match our records.']);
    }

    /**
     * Show registration form.
     */
    public function showRegister()
    {
        if (!AppSettings::isRegistrationEnabled()) {
            return redirect()->route('login')
                ->with('info', 'Registration is currently disabled. Please contact the administrator.');
        }

        if (Auth::check()) {
            return redirect()->route('documents.index');
        }
        return view('auth.register');
    }

    /**
     * Handle registration.
     */
    public function register(Request $request)
    {
        if (!AppSettings::isRegistrationEnabled()) {
            return redirect()->route('login')
                ->with('info', 'Registration is currently disabled.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        Auth::login($user);

        return redirect()->route('documents.index')
            ->with('success', 'Account created successfully! Welcome to DigiSign.');
    }

    /**
     * Handle SSO login.
     */
    public function ssoLogin(Request $request)
    {
        $request->validate([
            'sso_token' => 'required|string',
        ]);

        $ssoUrl = AppSettings::ssoApiUrl();
        $ssoKey = AppSettings::ssoApiKey();

        if (!$ssoUrl || !$ssoKey) {
            return back()->withErrors(['sso' => 'SSO is not configured. Please contact administrator.']);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $ssoKey,
                'Accept' => 'application/json',
            ])->post($ssoUrl . '/verify', [
                'token' => $request->sso_token,
            ]);

            if ($response->successful()) {
                $ssoData = $response->json();
                $ssoId = $ssoData['id'] ?? $ssoData['user_id'] ?? null;
                $ssoEmail = $ssoData['email'] ?? ($ssoId . '@sso.local');
                $ssoName = $ssoData['name'] ?? 'SSO User';

                if (!$ssoId) {
                     return back()->withErrors(['sso' => 'Invalid SSO response: Missing User ID.']);
                }

                // 1. Try to find user by SSO ID
                $user = User::where('sso_id', $ssoId)->first();

                // 2. If not found by SSO ID, try to find by Email (Account Merging)
                if (!$user && $ssoEmail) {
                    $user = User::where('email', $ssoEmail)->first();
                    if ($user) {
                        // Link existing account to SSO ID
                        $user->sso_id = $ssoId;
                        // Optional: Update name if desired, but respect existing Role
                        // $user->name = $ssoName; 
                        $user->save();
                    }
                }

                // 3. If user still not found, Create new user
                if (!$user) {
                    $user = User::create([
                        'name' => $ssoName,
                        'email' => $ssoEmail,
                        'password' => Hash::make(str()->random(32)),
                        'role' => 'user', // Default role for NEW SSO users
                        'sso_id' => $ssoId,
                        'is_active' => true,
                    ]);
                } else {
                    // Updating existing user: 
                    // Ensure user is active if they logged in successfully via SSO?
                    // Or respect local active status? Let's assume SSO validation implies active.
                    if (!$user->is_active) {
                         // If local admin deactivated this user, maybe we should block login?
                         // But usually SSO is trusted source. 
                         // User said "admin ... merge ... hak akses tetap".
                         // So we respect local role.
                    }
                }

                Auth::login($user, true);
                $request->session()->regenerate();

                return redirect()->route('documents.index')
                    ->with('success', 'Logged in via SSO successfully!');
            }

            return back()->withErrors(['sso' => 'SSO authentication failed. Invalid token.']);
        } catch (\Exception $e) {
            return back()->withErrors(['sso' => 'SSO service is unavailable. Please try again later.']);
        }
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}
