<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthCookieTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is required for cookie auth feature tests.');
        }

        parent::setUp();

        config([
            'sanctum.stateful' => ['localhost:5173'],
            'session.driver' => 'array',
        ]);
    }

    public function test_login_uses_session_cookie_without_returning_token(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $role = Role::create(['name' => 'Admin']);
        $user = User::factory()->create([
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role_id' => $role->id,
        ]);

        $this->withHeader('Origin', 'http://localhost:5173')
            ->postJson('/api/login', [
                'username' => 'admin',
                'password' => 'password',
            ])
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonMissingPath('token');

        $this->withHeader('Origin', 'http://localhost:5173')
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    public function test_logout_invalidates_session_cookie(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $role = Role::create(['name' => 'Admin']);
        User::factory()->create([
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role_id' => $role->id,
        ]);

        $this->withHeader('Origin', 'http://localhost:5173')
            ->postJson('/api/login', [
                'username' => 'admin',
                'password' => 'password',
            ])
            ->assertOk();

        $this->withHeader('Origin', 'http://localhost:5173')
            ->postJson('/api/logout')
            ->assertOk();

        $this->withHeader('Origin', 'http://localhost:5173')
            ->getJson('/api/user')
            ->assertUnauthorized();
    }
}
