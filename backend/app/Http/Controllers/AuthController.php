<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login dan dapatkan token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        // Hanya Admin dan Operator yang boleh login
        $user->load('role:id,name');
        if (! $user->role || ! in_array($user->role->name, ['Admin', 'Operator'])) {
            throw ValidationException::withMessages([
                'username' => ['Akun Anda tidak memiliki akses ke dashboard.'],
            ]);
        }

        // Hapus token lama jika ada (opsional, biar bersih)
        $user->tokens()->delete();

        // Update last active
        $user->update(['last_active_at' => now()]);
        $user->load('role:id,name');

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Logout — hapus token saat ini.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

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
