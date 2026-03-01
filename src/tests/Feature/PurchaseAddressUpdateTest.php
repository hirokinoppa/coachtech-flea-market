<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureProfileIsComplete;
use App\Models\Item;
use App\Models\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseAddressUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function purchaseShowUrl(int $itemId): string
    {
        return "/purchase/{$itemId}";
    }

    private function purchaseAddressEditUrl(int $itemId): string
    {
        return "/purchase/address/{$itemId}";
    }

    private function purchaseAddressUpdateUrl(int $itemId): string
    {
        return "/purchase/address/{$itemId}";
    }

    public function test_user_can_update_shipping_address_and_it_is_reflected_on_purchase_page(): void
    {
        $this->withoutMiddleware([
            EnsureEmailIsVerified::class,
            EnsureProfileIsComplete::class,
        ]);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $item = Item::factory()->create();

        $payload = [
            'postal_code' => '123-4567',
            'address'     => '東京都テスト1-2-3',
            'building'    => 'テストビル101',
        ];

        $response = $this->from($this->purchaseAddressEditUrl($item->id))
            ->post($this->purchaseAddressUpdateUrl($item->id), $payload);

        $response->assertRedirect($this->purchaseShowUrl($item->id));

        $response->assertSessionHas("purchase.shipping.{$item->id}", [
            'postal_code' => '123-4567',
            'address'     => '東京都テスト1-2-3',
            'building'    => 'テストビル101',
        ]);

        $r2 = $this->get($this->purchaseShowUrl($item->id));
        $r2->assertOk();

        $r2->assertSee('123-4567');
        $r2->assertSee('東京都テスト1-2-3');
        $r2->assertSee('テストビル101');
    }

    public function test_postal_code_is_required(): void
    {
        $this->withoutMiddleware([
            EnsureEmailIsVerified::class,
            EnsureProfileIsComplete::class,
        ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from($this->purchaseAddressEditUrl($item->id))
            ->post($this->purchaseAddressUpdateUrl($item->id), [
                'postal_code' => '',
                'address'     => '東京都テスト1-2-3',
                'building'    => 'テストビル101',
            ]);

        $response->assertRedirect($this->purchaseAddressEditUrl($item->id));
        $response->assertSessionHasErrors(['postal_code']);
    }

    public function test_address_is_required(): void
    {
        $this->withoutMiddleware([
            EnsureEmailIsVerified::class,
            EnsureProfileIsComplete::class,
        ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from($this->purchaseAddressEditUrl($item->id))
            ->post($this->purchaseAddressUpdateUrl($item->id), [
                'postal_code' => '123-4567',
                'address'     => '',
                'building'    => 'テストビル101',
            ]);

        $response->assertRedirect($this->purchaseAddressEditUrl($item->id));
        $response->assertSessionHasErrors(['address']);
    }
}