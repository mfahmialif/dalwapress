<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function general()
    {
        return response()->json($this->generalPayload());
    }

    public function publicSettings()
    {
        return response()->json([
            'system_name' => AppSetting::getValue('system_name', 'UII Dalwa Press'),
            'favicon_url' => $this->settingAssetUrl('favicon_path', 'favicon_url'),
            'landing_hero_image_url' => $this->settingAssetUrl('landing_hero_image_path', 'landing_hero_image_url'),
            'login_background_image_url' => $this->settingAssetUrl('login_background_image_path', 'login_background_image_url'),
            'navbar_brand_mode' => AppSetting::getValue('navbar_brand_mode', 'text'),
            'navbar_brand_icon' => AppSetting::getValue('navbar_brand_icon', 'auto_stories'),
            'navbar_brand_title' => AppSetting::getValue('navbar_brand_title', 'UII Dalwa'),
            'navbar_brand_subtitle' => AppSetting::getValue('navbar_brand_subtitle', 'Press Publisher'),
            'navbar_brand_logo_url' => $this->settingAssetUrl('navbar_brand_logo_path', 'navbar_brand_logo_url'),
            'footer_contact_email' => AppSetting::getValue('footer_contact_email', 'press@uiidalwa.ac.id'),
            'footer_contact_phone' => AppSetting::getValue('footer_contact_phone', '+62 812 0000 0000'),
            'footer_contact_location' => AppSetting::getValue('footer_contact_location', 'Pasuruan, Jawa Timur'),
            'footer_contact_maps_url' => AppSetting::getValue('footer_contact_maps_url', 'https://maps.app.goo.gl/pNNU4dnCtuu7y4Qk6'),
        ]);
    }

    public function updateGeneral(Request $request)
    {
        $data = $request->validate([
            'system_name' => 'required|string|max:120',
            'accent_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'favicon' => 'nullable|file|mimes:ico,png,jpg,jpeg,svg,webp|max:1024',
            'landing_hero_image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
            'login_background_image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
            'navbar_brand_mode' => ['required', Rule::in(['text', 'logo'])],
            'navbar_brand_icon' => ['nullable', 'required_if:navbar_brand_mode,text', 'string', 'max:64', 'regex:/^[a-z0-9_]+$/'],
            'navbar_brand_title' => 'nullable|required_if:navbar_brand_mode,text|string|max:80',
            'navbar_brand_subtitle' => 'nullable|string|max:80',
            'navbar_brand_logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'footer_contact_email' => 'required|email|max:255',
            'footer_contact_phone' => 'required|string|max:40',
            'footer_contact_location' => 'required|string|max:160',
            'footer_contact_maps_url' => 'nullable|url|max:500',
        ]);

        AppSetting::setValue('system_name', $data['system_name']);
        AppSetting::setValue('accent_color', strtolower($data['accent_color']));
        AppSetting::setValue('navbar_brand_mode', $data['navbar_brand_mode']);
        AppSetting::setValue('navbar_brand_icon', $data['navbar_brand_icon'] ?? 'auto_stories');
        AppSetting::setValue('navbar_brand_title', $data['navbar_brand_title'] ?? 'UII Dalwa');
        AppSetting::setValue('navbar_brand_subtitle', $data['navbar_brand_subtitle'] ?? '');
        AppSetting::setValue('footer_contact_email', $data['footer_contact_email']);
        AppSetting::setValue('footer_contact_phone', $data['footer_contact_phone']);
        AppSetting::setValue('footer_contact_location', $data['footer_contact_location']);
        AppSetting::setValue('footer_contact_maps_url', $data['footer_contact_maps_url'] ?? '');

        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('settings', 'public');
            AppSetting::setValue('favicon_path', $path);
            AppSetting::setValue('favicon_url', '/storage/'.ltrim($path, '/'));
        }

        $this->storeImageSetting(
            $request,
            'landing_hero_image',
            'landing_hero_image_path',
            'landing_hero_image_url',
        );
        $this->storeImageSetting(
            $request,
            'login_background_image',
            'login_background_image_path',
            'login_background_image_url',
        );
        $this->storeImageSetting(
            $request,
            'navbar_brand_logo',
            'navbar_brand_logo_path',
            'navbar_brand_logo_url',
        );

        return response()->json($this->generalPayload());
    }

    public function email()
    {
        return response()->json([
            'smtp_enabled' => AppSetting::getValue('smtp_enabled', false),
            'smtp_host' => AppSetting::getValue('smtp_host', ''),
            'smtp_port' => AppSetting::getValue('smtp_port', 587),
            'smtp_username' => AppSetting::getValue('smtp_username', ''),
            'smtp_password' => AppSetting::getValue('smtp_password', ''),
            'smtp_encryption' => AppSetting::getValue('smtp_encryption', 'tls'),
            'smtp_from_address' => AppSetting::getValue('smtp_from_address', ''),
            'smtp_from_name' => AppSetting::getValue('smtp_from_name', AppSetting::getValue('system_name', 'UII Dalwa Press')),
            'email_registration_default_role_id' => AppSetting::getValue('email_registration_default_role_id'),
            'roles' => $this->dashboardRoles(),
        ]);
    }

    public function updateEmail(Request $request)
    {
        $data = $request->validate([
            'smtp_enabled' => 'required|boolean',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'required|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:500',
            'smtp_encryption' => ['nullable', Rule::in(['', 'tls', 'ssl'])],
            'smtp_from_address' => 'nullable|required_if:smtp_enabled,true|email|max:255',
            'smtp_from_name' => 'nullable|required_if:smtp_enabled,true|string|max:255',
            'email_registration_default_role_id' => [
                'nullable',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('status', 'Active')),
            ],
        ]);

        AppSetting::setValue('smtp_enabled', $data['smtp_enabled'], 'boolean');
        AppSetting::setValue('smtp_host', $data['smtp_host'] ?? '');
        AppSetting::setValue('smtp_port', $data['smtp_port'], 'integer');
        AppSetting::setValue('smtp_username', $data['smtp_username'] ?? '');
        AppSetting::setValue('smtp_password', $data['smtp_password'] ?? '');
        AppSetting::setValue('smtp_encryption', $data['smtp_encryption'] ?? '');
        AppSetting::setValue('smtp_from_address', $data['smtp_from_address'] ?? '');
        AppSetting::setValue('smtp_from_name', $data['smtp_from_name'] ?? '');
        AppSetting::setValue('email_registration_default_role_id', $data['email_registration_default_role_id'] ?? null, 'integer');

        return $this->email();
    }

    public function googleLogin()
    {
        return response()->json([
            'google_login_enabled' => AppSetting::getValue('google_login_enabled', false),
            'google_client_id' => AppSetting::getValue('google_client_id', ''),
            'google_client_secret' => AppSetting::getValue('google_client_secret', ''),
            'google_hosted_domain' => AppSetting::getValue('google_hosted_domain', ''),
            'google_auto_create_users' => AppSetting::getValue('google_auto_create_users', false),
            'google_default_role_id' => AppSetting::getValue('google_default_role_id'),
            'roles' => Role::query()
                ->where('status', 'Active')
                ->whereIn('name', ['Admin', 'Operator', 'Author', 'Editor', 'Penulis', 'Kepala Penulis'])
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function updateGoogleLogin(Request $request)
    {
        $data = $request->validate([
            'google_login_enabled' => 'required|boolean',
            'google_client_id' => 'nullable|string|max:500',
            'google_client_secret' => 'nullable|string|max:500',
            'google_hosted_domain' => 'nullable|string|max:255',
            'google_auto_create_users' => 'required|boolean',
            'google_default_role_id' => [
                'nullable',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('status', 'Active')),
            ],
        ]);

        AppSetting::setValue('google_login_enabled', $data['google_login_enabled'], 'boolean');
        AppSetting::setValue('google_client_id', $data['google_client_id'] ?? '');
        AppSetting::setValue('google_client_secret', $data['google_client_secret'] ?? '');
        AppSetting::setValue('google_hosted_domain', $data['google_hosted_domain'] ?? '');
        AppSetting::setValue('google_auto_create_users', $data['google_auto_create_users'], 'boolean');
        AppSetting::setValue('google_default_role_id', $data['google_default_role_id'] ?? null, 'integer');

        return $this->googleLogin();
    }

    private function generalPayload(): array
    {
        return [
            'system_name' => AppSetting::getValue('system_name', 'UII Dalwa Press'),
            'accent_color' => AppSetting::getValue('accent_color', '#2563eb'),
            'favicon_url' => $this->settingAssetUrl('favicon_path', 'favicon_url'),
            'landing_hero_image_url' => $this->settingAssetUrl('landing_hero_image_path', 'landing_hero_image_url'),
            'login_background_image_url' => $this->settingAssetUrl('login_background_image_path', 'login_background_image_url'),
            'navbar_brand_mode' => AppSetting::getValue('navbar_brand_mode', 'text'),
            'navbar_brand_icon' => AppSetting::getValue('navbar_brand_icon', 'auto_stories'),
            'navbar_brand_title' => AppSetting::getValue('navbar_brand_title', 'UII Dalwa'),
            'navbar_brand_subtitle' => AppSetting::getValue('navbar_brand_subtitle', 'Press Publisher'),
            'navbar_brand_logo_url' => $this->settingAssetUrl('navbar_brand_logo_path', 'navbar_brand_logo_url'),
            'footer_contact_email' => AppSetting::getValue('footer_contact_email', 'press@uiidalwa.ac.id'),
            'footer_contact_phone' => AppSetting::getValue('footer_contact_phone', '+62 812 0000 0000'),
            'footer_contact_location' => AppSetting::getValue('footer_contact_location', 'Pasuruan, Jawa Timur'),
            'footer_contact_maps_url' => AppSetting::getValue('footer_contact_maps_url', 'https://maps.app.goo.gl/pNNU4dnCtuu7y4Qk6'),
        ];
    }

    private function settingAssetUrl(string $pathKey, string $urlKey): string
    {
        $path = AppSetting::getValue($pathKey);

        if ($path) {
            return '/storage/'.ltrim($path, '/');
        }

        return (string) AppSetting::getValue($urlKey, '');
    }

    private function storeImageSetting(
        Request $request,
        string $inputName,
        string $pathKey,
        string $urlKey,
    ): void {
        if (! $request->hasFile($inputName)) {
            return;
        }

        $disk = Storage::disk('public');
        $previousPath = AppSetting::getValue($pathKey);
        $path = $request->file($inputName)->store('settings', 'public');

        AppSetting::setValue($pathKey, $path);
        AppSetting::setValue($urlKey, '/storage/'.ltrim($path, '/'));

        if ($previousPath && $previousPath !== $path) {
            $disk->delete($previousPath);
        }
    }

    private function dashboardRoles()
    {
        return Role::query()
            ->where('status', 'Active')
            ->whereIn('name', ['Admin', 'Operator', 'Author', 'Editor', 'Penulis', 'Kepala Penulis'])
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
