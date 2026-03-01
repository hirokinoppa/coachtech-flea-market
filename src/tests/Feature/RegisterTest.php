<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    private bool $debugOnFail = true;

    private function postRegister(array $data, string $from = '/register')
    {
        return $this->from($from)
            ->withHeader('Accept', 'text/html')
            ->post('/register', $data);
    }

    private function failFastIfServerError($response): void
    {
        if (!$this->debugOnFail) {
            return;
        }

        if ($response->status() >= 500) {
            if (property_exists($response, 'exception') && $response->exception) {
                throw $response->exception;
            }

            $this->fail(
                "500 error occurred but exception was not attached.\n" .
                "Check: storage/logs/laravel.log"
            );
        }
    }

    private function assertValidationRedirect($response, array $keys, string $redirectPath = '/register'): void
    {
        $response->assertRedirect($redirectPath);
        $response->assertSessionHasErrors($keys);
    }

    public function test_user_can_register(): void
    {
        if ($this->debugOnFail) {
            $this->withoutExceptionHandling();
        }

        $response = $this->postRegister([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->failFastIfServerError($response);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name'  => 'テスト太郎',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice'));
    }

    public function test_name_is_required(): void
    {
        $response = $this->postRegister([
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertValidationRedirect($response, ['name']);
        $this->assertGuest();
    }

    public function test_email_is_required(): void
    {
        $response = $this->postRegister([
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertValidationRedirect($response, ['email']);
        $this->assertGuest();
    }

    public function test_password_is_required(): void
    {
        $response = $this->postRegister([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $this->assertValidationRedirect($response, ['password']);
        $this->assertGuest();
    }

    public function test_password_must_be_at_least_8_characters(): void
    {
        $response = $this->postRegister([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ]);

        $this->assertValidationRedirect($response, ['password']);
        $this->assertGuest();
    }

    public function test_password_confirmation_must_match(): void
    {
        $response = $this->postRegister([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password999',
        ]);

        $this->assertValidationRedirect($response, ['password']);
        $this->assertGuest();
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postRegister([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertValidationRedirect($response, ['email']);
        $this->assertGuest();
    }
}