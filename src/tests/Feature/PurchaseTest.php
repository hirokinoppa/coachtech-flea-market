<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureProfileIsComplete;
use App\Models\Item;
use App\Models\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    private function purchaseUrl(int $itemId): string
    {
        return "/purchase/{$itemId}";
    }

    private function purchaseSuccessUrl(string $sessionId): string
    {
        return "/purchase/success?session_id={$sessionId}";
    }

    private function setShippingSession(int $itemId): void
    {
        $this->withSession([
            "purchase.shipping.{$itemId}" => [
                'postal_code' => '123-4567',
                'address'     => '東京都テスト1-2-3',
                'building'    => 'テストビル101',
            ],
        ]);
    }

    private function mockStripeCreateSession(string $redirectUrl = 'https://example.com/stripe'): void
    {
        Mockery::mock('alias:Stripe\Stripe')
            ->shouldReceive('setApiKey')
            ->andReturnNull();

        Mockery::mock('alias:Stripe\Checkout\Session')
            ->shouldReceive('create')
            ->andReturn((object) ['url' => $redirectUrl]);
    }

    private function mockStripeRetrieveSessionPaid(int $itemId, int $buyerId, string $paymentMethod = 'card'): void
    {
        Mockery::mock('alias:Stripe\Stripe')
            ->shouldReceive('setApiKey')
            ->andReturnNull();

        Mockery::mock('alias:Stripe\Checkout\Session')
            ->shouldReceive('retrieve')
            ->andReturn((object) [
                'payment_status' => 'paid',
                'metadata' => (object) [
                    'item_id'         => (string) $itemId,
                    'buyer_id'        => (string) $buyerId,
                    'payment_method'  => $paymentMethod,
                ],
            ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_user_can_start_purchase_and_redirects_to_stripe(): void
    {
        $this->withoutMiddleware([
            EnsureEmailIsVerified::class,
            EnsureProfileIsComplete::class,
        ]);

        $this->mockStripeCreateSession('https://example.com/stripe');

        $buyer = User::factory()->create(['email_verified_at' => now()]);
        $seller = User::factory()->create();

        $item = Item::factory()->create([
            'user_id'  => $seller->id,
            'is_sold'  => false,
        ]);

        $this->actingAs($buyer);
        $this->setShippingSession($item->id);

        $response = $this->from($this->purchaseUrl($item->id))
            ->post($this->purchaseUrl($item->id), [
                'payment_method' => 'card',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('https://example.com/stripe');
    }

    public function test_payment_method_is_required(): void
    {
        $this->withoutMiddleware([
            EnsureEmailIsVerified::class,
            EnsureProfileIsComplete::class,
        ]);

        $buyer = User::factory()->create(['email_verified_at' => now()]);
        $seller = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'is_sold' => false,
        ]);

        $this->actingAs($buyer);
        $this->setShippingSession($item->id);

        $response = $this->from($this->purchaseUrl($item->id))
            ->post($this->purchaseUrl($item->id), []);

        if ($response->isRedirect()) {
            $response->assertSessionHasErrors(['payment_method']);
            return;
        }

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_method']);
    }

    public function test_purchase_success_creates_order_and_marks_item_sold(): void
    {
        $this->withoutMiddleware([
            EnsureEmailIsVerified::class,
            EnsureProfileIsComplete::class,
        ]);

        $buyer = User::factory()->create(['email_verified_at' => now()]);
        $seller = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'is_sold' => false,
        ]);

        $this->actingAs($buyer);
        $this->setShippingSession($item->id);

        $this->mockStripeRetrieveSessionPaid($item->id, $buyer->id, 'card');

        $response = $this->get($this->purchaseSuccessUrl('cs_test_123'));

        $response->assertRedirect('/');

        $item->refresh();
        $this->assertTrue((bool) $item->is_sold);

        $this->assertDatabaseHas('orders', [
            'item_id'               => $item->id,
            'buyer_id'              => $buyer->id,
            'seller_id'             => $seller->id,
            'price'                 => (int) $item->price,
            'payment_method'        => 2,
            'status'                => 1,
            'shipping_postal_code'  => '123-4567',
            'shipping_address'      => '東京都テスト1-2-3',
            'shipping_building'     => 'テストビル101',
        ]);
    }
}