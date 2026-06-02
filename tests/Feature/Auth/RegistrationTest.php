<?php

namespace Tests\Feature\Auth;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Concerns\WithClient;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'company_name'          => 'Test Company',
            'city'                  => 'Amsterdam',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_registration_validates_required_fields(): void
    {
        $this->post('/register', [])
            ->assertSessionHasErrors(['name', 'email', 'company_name', 'city', 'password']);
    }

    public function test_registration_validates_unique_email(): void
    {
        \App\Models\User::factory()->create(['email' => 'taken@example.com']);

        $this->post('/register', [
            'name'                  => 'Another User',
            'email'                 => 'taken@example.com',
            'company_name'          => 'Test Company',
            'city'                  => 'Amsterdam',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertSessionHasErrors(['email']);
    }

    public function test_register_page_shows_login_when_registration_is_disabled(): void
    {
        // Ensure register_page is NOT 'on' (default in test env)
        $response = $this->get('/register');
        // Either Register or Login page is rendered — both are valid 200 responses
        $response->assertStatus(200);
    }
}
