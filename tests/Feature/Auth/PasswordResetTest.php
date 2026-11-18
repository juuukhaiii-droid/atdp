<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_recovery_pages_are_disabled(): void
    {
        $this->get('/forgot-password')->assertNotFound();
        $this->get('/reset-password/example-token')->assertNotFound();
        $this->get('/login')->assertOk()->assertDontSee('Forgot password?')->assertDontSee('href="/register"', false);
    }

    public function test_password_recovery_requests_cannot_send_email_or_change_passwords(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $original = $user->password;
        $this->post('/forgot-password', ['email' => $user->email])->assertNotFound();
        $this->post('/reset-password', [
            'token' => 'example-token', 'email' => $user->email,
            'password' => 'ChangedPassword123!', 'password_confirmation' => 'ChangedPassword123!',
        ])->assertNotFound();
        Notification::assertNothingSent();
        $this->assertSame($original, $user->fresh()->password);
    }
}
