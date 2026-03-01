<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    private function locationPath(string $location): string
    {
        $path = parse_url($location, PHP_URL_PATH);

        if ($path === null || $path === '') {
            return '/';
        }

        return $path;
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect();

        $location = $response->headers->get('Location') ?? '';
        $path = $this->locationPath($location);

        $this->assertTrue(
            in_array($path, ['/', '/login'], true),
            "リダイレクト先が想定外です。Location={$location} / path={$path}"
        );
    }

    public function test_guest_is_redirected_when_posting_logout(): void
    {
        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect();
    }
}