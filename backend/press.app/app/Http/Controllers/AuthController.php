<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login dan buat session cookie.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'remember' => 'sometimes|boolean',
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        $user->load('role:id,name');
        if (! $user->role || ! in_array($user->role->name, ['Admin', 'Operator', 'Author', 'Editor'])) {
            throw ValidationException::withMessages([
                'username' => ['Akun Anda tidak memiliki akses ke dashboard.'],
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Update last active
        $user->update(['last_active_at' => now()]);
        $user->load('role:id,name');

        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Logout dan hapus session saat ini.
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Berhasil logout.',
        ]);
    }

    /**
     * Ambil data user yang sedang login.
     */
    public function user(Request $request)
    {
        $request->user()->load('role:id,name');
        return response()->json($request->user());
    }
}
