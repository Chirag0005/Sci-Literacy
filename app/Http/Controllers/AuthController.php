<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            \App\Services\AuditLogService::log('LOGIN_SUCCESS', [
                'user_id' => Auth::id(),
                'email' => $credentials['email']
            ]);
            return redirect()->route('dashboard')->with('success', 'Welcome back!');
        }

        \App\Services\AuditLogService::log('LOGIN_FAILED', [
            'email' => $credentials['email']
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check uniqueness in MongoDB
        $existing = \App\Services\MongoService::execute('findOne', 'users', ['email' => $request->email]);
        if ($existing) {
            return back()->withErrors([
                'email' => 'The email has already been taken.',
            ])->onlyInput('email');
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => false,
            'xp' => 0,
            'streak' => 0,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String()
        ];

        $insertResult = \App\Services\MongoService::execute('insert', 'users', [], $userData);
        $userData['_id'] = $insertResult['insertedId'];

        $user = new User();
        $user->forceFill($userData);
        $user->id = $userData['_id'];

        \App\Services\AuditLogService::log('USER_REGISTERED', [
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name
        ]);

        Auth::login($user);

        \App\Services\AuditLogService::log('LOGIN_SUCCESS', [
            'user_id' => $user->id,
            'email' => $user->email,
            'context' => 'registration_auto_login'
        ]);

        return redirect()->route('dashboard')->with('success', 'Account created successfully!');
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();
        $email = Auth::user()?->email;

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        \App\Services\AuditLogService::log('LOGOUT', [
            'user_id' => $userId,
            'email' => $email
        ]);

        return redirect()->route('home')->with('success', 'Logged out successfully.');
    }
}
