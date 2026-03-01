<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    private string $editUrl = '/mypage/profile';
    private string $updateUrl = '/mypage/profile';

    public function test_guest_is_redirected_from_profile_edit(): void
    {
        $response = $this->get($this->editUrl);

        $response->assertRedirect();
        $this->assertStringEndsWith('/login', $response->headers->get('Location') ?? '');
    }

    public function test_profile_edit_shows_initial_values(): void
    {
        $user = User::factory()->create([
            'name' => '初期ユーザー名(Users)',
            'email_verified_at' => now(),
        ]);

        Profile::factory()->create([
            'user_id'      => $user->id,
            'name'         => '初期ユーザー名(Profile)',
            'postal_code'  => '123-4567',
            'address'      => '東京都テスト1-2-3',
            'building'     => 'テストビル101',
        ]);

        $this->actingAs($user);

        $response = $this->get($this->editUrl);
        $response->assertOk();

        $body = $response->getContent();
        $this->assertTrue(
            str_contains($body, '初期ユーザー名(Users)') || str_contains($body, '初期ユーザー名(Profile)'),
            'ユーザー名が画面に表示されていません'
        );

        $response->assertSee('123-4567');
        $response->assertSee('東京都テスト1-2-3');
        $response->assertSee('テストビル101');
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => '更新前ユーザー(Users)',
            'email_verified_at' => now(),
        ]);

        Profile::factory()->create([
            'user_id'     => $user->id,
            'name'        => '更新前ユーザー(Profile)',
            'postal_code' => '000-0000',
            'address'     => '更新前住所',
            'building'    => '更新前建物',
        ]);

        $this->actingAs($user);

        $response = $this->from($this->editUrl)->post($this->updateUrl, [
            'name'        => '更新後ユーザー',
            'postal_code' => '987-6543',
            'address'     => '大阪府テスト9-8-7',
            'building'    => 'なんばビル202',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('profiles', [
            'user_id'      => $user->id,
            'name'         => '更新後ユーザー',
            'postal_code'  => '987-6543',
            'address'      => '大阪府テスト9-8-7',
            'building'     => 'なんばビル202',
        ]);

        $user->refresh();
        $profile = Profile::where('user_id', $user->id)->first();

        $this->assertNotNull($profile);
        $this->assertSame('更新後ユーザー', $profile->name);
    }

    public function test_profile_update_validation_required(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Profile::factory()->create([
            'user_id'     => $user->id,
            'name'        => '初期(Profile)',
            'postal_code' => '123-4567',
            'address'     => '東京都テスト1-2-3',
        ]);

        $this->actingAs($user);

        $response = $this->from($this->editUrl)->post($this->updateUrl, [
            'name'        => '',
            'postal_code' => '',
            'address'     => '',
        ]);

        $response->assertRedirect($this->editUrl);
        $response->assertSessionHasErrors(['name', 'postal_code', 'address']);
    }
}