<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'encryption_salt' => User::generateEncryptionSalt(),
        ]);

        Auth::login($user);

        // Return user info for client-side diary_token generation
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'encryption_salt' => $user->encryption_salt,
            ],
            'redirect' => route('diary.index'),
        ]);
    }

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($validated, $request->boolean('remember'))) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // Generate encryption_salt if missing (for users created before this feature)
        if (empty($user->encryption_salt)) {
            $user->encryption_salt = User::generateEncryptionSalt();
            $user->save();
        }

        // Return user info for client-side diary_token generation
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'encryption_salt' => $user->encryption_salt,
            ],
            'redirect' => route('diary.index'),
        ]);
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Get current user info (for page refresh scenarios).
     */
    public function me(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'authenticated' => false,
            ], 401);
        }

        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'encryption_salt' => $user->encryption_salt,
            ],
        ]);
    }
}
