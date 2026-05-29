<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * BAN-200: verify the four ported auth pages render the correct Inertia
 * component. Existing AuthenticationTest, RegistrationTest, PasswordResetTest,
 * EmailVerificationTest cover the form-submission paths — these tests assert
 * the new render shape without re-testing what already passes.
 */
class InertiaAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_renders_inertia_auth_login_component(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));
    }

    public function test_forgot_password_renders_inertia_component(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Auth/ForgotPassword'));
    }

    public function test_reset_password_renders_inertia_component_with_token_and_email(): void
    {
        $this->get('/reset-password/abc123?email=user@example.com')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/ResetPassword')
                ->where('token', 'abc123')
                ->where('email', 'user@example.com')
            );
    }

    public function test_verify_email_renders_inertia_component_when_unverified(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Auth/VerifyEmail'));
    }

    public function test_verify_email_redirects_when_already_verified(): void
    {
        $user = User::factory()->create();   // verified by default

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertRedirect();
    }
}
