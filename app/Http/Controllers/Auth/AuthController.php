<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Inertia\Inertia;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check if user exists and is active
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect or account is inactive.'],
            ]);
        }

        // Attempt login
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Update last login time
            $user->update(['last_login_at' => now()]);
            
            // Redirect based on role
            return $this->redirectBasedOnRole($user);
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }

    /**
     * Redirect user based on their role
     */
    private function redirectBasedOnRole(User $user)
    {
        return match($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'nurse' => redirect()->route('ehr.index'),
            'inventory_manager' => redirect()->route('inventory.index'),
            'account_manager' => redirect()->route('users.index'),
            default => redirect()->route('dashboard'),
        };
    }

    /**
     * Get current user info
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
