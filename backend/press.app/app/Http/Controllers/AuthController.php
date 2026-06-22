<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const DASHBOARD_ROLES = ['Admin', 'Operator', 'Author', 'Editor', 'Penulis', 'Kepala Penulis'];

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

        if ($user->status !== 'Active') {
            throw ValidationException::withMessages([
                'username' => ['Akun Anda tidak aktif.'],
            ]);
        }

        $user->load('role:id,name');
        if (! $user->role || ! in_array($user->role->name, ['Admin', 'Operator', 'Author', 'Editor', 'Penulis', 'Kepala Penulis'])) {
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

    public function googleConfig()
    {
        $enabled = AppSetting::getValue('google_login_enabled', false);
        $clientId = AppSetting::getValue('google_client_id', '');

        return response()->json([
            'enabled' => (bool) $enabled && filled($clientId),
            'client_id' => $enabled ? $clientId : null,
        ]);
    }

    public function googleLogin(Request $request)
    {
        $request->validate([
            'credential' => 'required|string',
        ]);

        $enabled = AppSetting::getValue('google_login_enabled', false);
        $clientId = AppSetting::getValue('google_client_id', '');

        if (! $enabled || blank($clientId)) {
            throw ValidationException::withMessages([
                'google' => ['Login Google belum diaktifkan.'],
            ]);
        }

        $payload = $this->verifyGoogleCredential($request->credential, $clientId);
        $email = $payload['email'] ?? null;
        $googleId = $payload['sub'] ?? null;

        if (blank($email) || blank($googleId)) {
            throw ValidationException::withMessages([
                'google' => ['Akun Google tidak valid.'],
            ]);
        }

        $hostedDomain = trim((string) AppSetting::getValue('google_hosted_domain', ''));
        if ($hostedDomain !== '' && ! $this->matchesHostedDomain($payload, $email, $hostedDomain)) {
            throw ValidationException::withMessages([
                'google' => ["Akun Google harus memakai domain {$hostedDomain}."],
            ]);
        }

        $user = User::query()
            ->where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if (! $user) {
            if (! AppSetting::getValue('google_auto_create_users', false)) {
                throw ValidationException::withMessages([
                    'google' => ['Akun Google belum terdaftar di sistem.'],
                ]);
            }

            $user = $this->createGoogleUser($payload);
        }

        if ($user->status !== 'Active') {
            throw ValidationException::withMessages([
                'google' => ['Akun Anda tidak aktif.'],
            ]);
        }

        $user->load('role:id,name');
        if (! $user->role || ! in_array($user->role->name, self::DASHBOARD_ROLES, true)) {
            throw ValidationException::withMessages([
                'google' => ['Akun Anda tidak memiliki akses ke dashboard.'],
            ]);
        }

        $user->forceFill([
            'google_id' => $googleId,
            'avatar_url' => $payload['picture'] ?? $user->avatar_url,
            'last_active_at' => now(),
        ])->save();

        Auth::login($user);
        $request->session()->regenerate();

        $user->load('role:id,name');

        return response()->json([
            'user' => $user,
        ]);
    }

    public function checkUsername(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:255',
        ]);

        return response()->json([
            'available' => ! User::where('username', $data['username'])->exists(),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'username' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_]+$/', Rule::unique('users', 'username')],
            'password' => 'required|string|min:6|confirmed',
        ]);

        $existing = User::where('email', $data['email'])->first();
        if ($existing && $existing->email_verified_at) {
            throw ValidationException::withMessages([
                'email' => ['Email sudah terdaftar. Silakan login.'],
            ]);
        }

        if ($existing && $existing->username !== $data['username']) {
            throw ValidationException::withMessages([
                'email' => ['Email ini sudah memiliki proses pendaftaran. Gunakan username sebelumnya atau hubungi admin.'],
            ]);
        }

        $roleId = $this->registrationRoleId();
        $code = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(15);

        $user = $existing ?: new User();
        $user->fill([
            'name' => $data['name'] ?: $data['username'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'role_id' => $roleId,
            'status' => 'Inactive',
            'email_verification_code' => $code,
            'email_verification_expires_at' => $expiresAt,
        ]);
        $user->save();

        $this->sendVerificationCode($user, $code);

        return response()->json([
            'message' => 'Kode verifikasi sudah dikirim ke email.',
            'email' => $user->email,
            'expires_at' => $expiresAt,
        ], 201);
    }

    public function verifyEmailCode(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user || $user->email_verification_code !== $data['code']) {
            throw ValidationException::withMessages([
                'code' => ['Kode verifikasi tidak valid.'],
            ]);
        }

        if (! $user->email_verification_expires_at || $user->email_verification_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'code' => ['Kode verifikasi sudah kedaluwarsa. Silakan daftar ulang untuk mengirim kode baru.'],
            ]);
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'email_verification_code' => null,
            'email_verification_expires_at' => null,
            'status' => 'Active',
            'last_active_at' => now(),
        ])->save();

        Auth::login($user);
        $request->session()->regenerate();

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

    private function verifyGoogleCredential(string $credential, string $clientId): array
    {
        try {
            $response = Http::timeout(8)->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $credential,
            ]);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'google' => ['Gagal memverifikasi akun Google. Coba lagi nanti.'],
            ]);
        }

        if (! $response->ok()) {
            throw ValidationException::withMessages([
                'google' => ['Token Google tidak valid atau sudah kedaluwarsa.'],
            ]);
        }

        $payload = $response->json();

        if (($payload['aud'] ?? null) !== $clientId) {
            throw ValidationException::withMessages([
                'google' => ['Client ID Google tidak sesuai.'],
            ]);
        }

        if (! filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            throw ValidationException::withMessages([
                'google' => ['Email Google belum terverifikasi.'],
            ]);
        }

        return $payload;
    }

    private function matchesHostedDomain(array $payload, string $email, string $hostedDomain): bool
    {
        $domain = Str::afterLast($email, '@');

        return strtolower($payload['hd'] ?? $domain) === strtolower($hostedDomain);
    }

    private function createGoogleUser(array $payload): User
    {
        $roleId = AppSetting::getValue('google_default_role_id');

        if (! $roleId || ! Role::whereKey($roleId)->where('status', 'Active')->exists()) {
            $roleId = Role::where('name', 'Author')->value('id')
                ?? Role::where('name', 'Penulis')->value('id')
                ?? Role::whereIn('name', self::DASHBOARD_ROLES)->value('id');
        }

        if (! $roleId) {
            throw ValidationException::withMessages([
                'google' => ['Role default untuk akun Google belum tersedia.'],
            ]);
        }

        return User::create([
            'username' => $this->uniqueUsername($payload['email']),
            'name' => $payload['name'] ?? Str::before($payload['email'], '@'),
            'email' => $payload['email'],
            'email_verified_at' => now(),
            'google_id' => $payload['sub'],
            'avatar_url' => $payload['picture'] ?? null,
            'password' => Hash::make(Str::random(48)),
            'role_id' => $roleId,
            'status' => 'Active',
            'last_active_at' => now(),
        ]);
    }

    private function uniqueUsername(string $email): string
    {
        $base = Str::of(Str::before($email, '@'))
            ->lower()
            ->replaceMatches('/[^a-z0-9_]+/', '_')
            ->trim('_')
            ->value() ?: 'google_user';

        $username = $base;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = "{$base}_{$counter}";
            $counter++;
        }

        return $username;
    }

    private function registrationRoleId(): int
    {
        $roleId = AppSetting::getValue('email_registration_default_role_id');

        if ($roleId && Role::whereKey($roleId)->where('status', 'Active')->exists()) {
            return (int) $roleId;
        }

        $roleId = Role::where('name', 'Author')->value('id')
            ?? Role::where('name', 'Penulis')->value('id')
            ?? Role::whereIn('name', self::DASHBOARD_ROLES)->value('id');

        if (! $roleId) {
            throw ValidationException::withMessages([
                'email' => ['Role default pendaftaran belum tersedia.'],
            ]);
        }

        return (int) $roleId;
    }

    private function sendVerificationCode(User $user, string $code): void
    {
        $enabled = AppSetting::getValue('smtp_enabled', false);
        if (! $enabled) {
            throw ValidationException::withMessages([
                'email' => ['SMTP belum diaktifkan di pengaturan email.'],
            ]);
        }

        $this->applyMailSettings();

        $systemName = AppSetting::getValue('system_name', 'UII Dalwa Press');
        $body = "Halo {$user->name},\n\nKode verifikasi akun {$systemName} Anda adalah: {$code}\n\nKode ini berlaku selama 15 menit.\n\nJika Anda tidak melakukan pendaftaran, abaikan email ini.";

        try {
            Mail::raw($body, function ($message) use ($user, $systemName) {
                $message->to($user->email, $user->name)
                    ->subject("Kode Verifikasi {$systemName}");
            });
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'email' => ['Gagal mengirim email verifikasi. Periksa pengaturan SMTP.'],
            ]);
        }
    }

    private function applyMailSettings(): void
    {
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', AppSetting::getValue('smtp_host', '127.0.0.1'));
        Config::set('mail.mailers.smtp.port', (int) AppSetting::getValue('smtp_port', 587));
        Config::set('mail.mailers.smtp.username', AppSetting::getValue('smtp_username', null));
        Config::set('mail.mailers.smtp.password', AppSetting::getValue('smtp_password', null));
        $encryption = strtolower((string) AppSetting::getValue('smtp_encryption', 'tls'));
        Config::set('mail.mailers.smtp.scheme', $encryption === 'ssl' ? 'smtps' : 'smtp');
        Config::set('mail.mailers.smtp.auto_tls', $encryption === 'tls');
        Config::set('mail.mailers.smtp.require_tls', $encryption === 'tls');
        Config::set('mail.from.address', AppSetting::getValue('smtp_from_address', 'no-reply@example.com'));
        Config::set('mail.from.name', AppSetting::getValue('smtp_from_name', AppSetting::getValue('system_name', 'UII Dalwa Press')));

        if (method_exists(app('mail.manager'), 'forgetMailers')) {
            app('mail.manager')->forgetMailers();
        }
    }
}
