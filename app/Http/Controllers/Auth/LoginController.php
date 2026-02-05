<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = $request->user();

            // Check if user is active
            if ($user->status !== 'active') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Compte inactif.',
                ])->onlyInput('email');
            }

            // Redirect based on role
            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            }

            if ($user->role === 'doctor') {
                return redirect()->intended(route('doctor.dashboard'));
            }

            // Patients and other roles cannot access web interface
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Acces restreint. Utilisez l\'application mobile.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'Identifiants invalides.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $role = $user?->role;

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
