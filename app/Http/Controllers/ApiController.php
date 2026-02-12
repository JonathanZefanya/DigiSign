<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class ApiController extends Controller
{
    /**
     * Verify user credentials for external apps (SSO Provider).
     * Expects Bearer Token (API Key) and user credentials.
     */
    public function ssoUser(Request $request)
    {
        Log::info('SSO User Verification Request', [
            'ip' => $request->ip(),
            'token_sample' => substr($request->bearerToken(), 0, 10) . '...',
            'email' => $request->email
        ]);

        // 1. Verify API Key
        $providerKey = Setting::get('provider_api_key');
        
        if (!$providerKey) {
            return response()->json(['error' => 'SSO Provider is not configured on the server.'], 503);
        }

        $token = $request->bearerToken();
        
        // Simple comparison (consider hash_equals for timing attacks protection in production)
        if ($token !== $providerKey) {
            return response()->json(['error' => 'Unauthorized: Invalid API Key'], 401);
        }

        // 2. Validate Input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // 3. Find User
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['error' => 'User account is inactive'], 403);
        }

        // 4. Return User Data
        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
            ]
        ]);
    }

    /**
     * SSO Auto-Login for Web-Tools integration.
     * Accepts email and name, creates user if not exists, then generates signed URL for auto-login.
     */
    public function ssoLogin(Request $request)
    {
        Log::info('SSO Auto-Login Request', [
            'ip' => $request->ip(),
            'token_sample' => substr($request->bearerToken(), 0, 10) . '...',
            'email' => $request->email,
            'name' => $request->name
        ]);

        // 1. Verify API Key
        $providerKey = Setting::get('provider_api_key');
        
        if (!$providerKey) {
            return response()->json([
                'errors' => [['title' => 'SSO Provider is not configured on the server.']]
            ], 503);
        }

        $token = $request->bearerToken();
        
        if (!$token || $token !== $providerKey) {
            return response()->json([
                'errors' => [['title' => 'Unauthorized: Invalid API Key']]
            ], 401);
        }

        // 2. Validate Input
        $email = $request->input('email');
        $name = $request->input('name');
        $redirect = $request->input('redirect', 'documents');

        if (empty($email) || empty($name)) {
            return response()->json([
                'errors' => [['title' => 'Email and name are required.']]
            ], 422);
        }

        // 3. Find or Create User
        // Use the email directly from Web-Tools (it should be actual email)
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Create new user with SSO
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(bin2hex(random_bytes(16))), // Random password
                'role' => 'user', // Default role
                'is_active' => true,
            ]);
            
            Log::info('New SSO user created', ['email' => $user->email, 'name' => $user->name]);
        }

        if (!$user->is_active) {
            return response()->json([
                'errors' => [['title' => 'User account is inactive']]
            ], 403);
        }

        // 4. Generate signed URL for auto-login (valid for 2 minutes)
        $signedUrl = URL::temporarySignedRoute(
            'sso.autologin',
            now()->addMinutes(2),
            [
                'user_id' => $user->id,
                'redirect' => $redirect
            ]
        );

        Log::info('SSO Auto-Login URL generated', [
            'user_id' => $user->id,
            'redirect' => $redirect
        ]);

        // 5. Return success with signed URL
        return response()->json([
            'data' => [
                'url' => $signedUrl,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ]
        ]);
    }

    /**
     * Handle SSO auto-login via signed URL
     */
    public function ssoAutoLogin(Request $request)
    {
        // Signature is validated by middleware
        $userId = $request->get('user_id');
        $redirect = $request->get('redirect', 'documents');

        $user = User::find($userId);

        if (!$user || !$user->is_active) {
            abort(403, 'User not found or inactive');
        }

        // Login the user
        Auth::login($user, true);

        Log::info('SSO Auto-Login successful', [
            'user_id' => $user->id,
            'redirect' => $redirect
        ]);

        // Redirect to requested page
        return redirect($redirect);
    }
}
