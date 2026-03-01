<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_notice_page_is_accessible_for_logged_in_user(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('verification.notice'));

        $response->assertStatus(200);
        $response->assertSee('メール');
        $response->assertSee('認証');
    }

    public function test_verify_email_is_sent_after_register(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'test@example.com')->firstOrFail();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_user_can_verify_email_via_signed_url_and_is_redirected_to_profile_edit(): void
    {
        Event::fake([Verified::class]);

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->get($url);

        $response->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);

        Event::assertDispatched(Verified::class);
    }

    public function test_invalid_signed_verification_url_is_forbidden(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $validUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $invalidUrl = $validUrl . 'x';

        $response = $this->get($invalidUrl);

        $response->assertStatus(403);
    }

    public function test_user_can_request_verification_email_resend(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('verification.send'));

        $response->assertRedirect();

        Notification::assertSentTo($user, VerifyEmail::class);
    }
}