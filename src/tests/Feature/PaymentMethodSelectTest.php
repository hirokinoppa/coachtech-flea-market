<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Http\Middleware\EnsureProfileIsComplete;
use Mockery;

class PaymentMethodSelectTest extends TestCase
{
    use RefreshDatabase;

    private function purchaseUrl(int $itemId): string
    {
        return "/purchase/{$itemId}";
    }

    private function disableBlockingMiddlewares(): void
    {
        $this->withoutMiddleware([
            EnsureEmailIsVerified::class,
            EnsureProfileIsComplete::class,
        ]);
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

    private function mockStripeCreateSessionWithAssert(
        callable $assertPayload,
        string $redirectUrl = 'https://example.com/stripe'
    ): void {
        Mockery::mock('alias:Stripe\Stripe')
            ->shouldReceive('setApiKey')
            ->andReturnNull();

        Mockery::mock('alias:Stripe\Checkout\Session')
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($payload) use ($assertPayload) {
                $assertPayload($payload);
                return true;
            }))
            ->andReturn((object) ['url' => $redirectUrl]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_select_card_is_reflected_in_stripe_payload(): void
    {
        $this->disableBlockingMiddlewares();

        $buyer  = User::factory()->create(['email_verified_at' => now()]);
        $seller = User::factory()->create();

        $item = Item::factory()->create([
            'user_id'  => $seller->id,
            'is_sold'  => false,
        ]);

        $this->actingAs($buyer);
        $this->setShippingSession($item->id);

        $this->mockStripeCreateSessionWithAssert(function ($payload) use ($item, $buyer) {
            $this->assertEquals(['card'], $payload['payment_method_types']);

            $this->assertSame((string) $item->id, (string) ($payload['metadata']['item_id'] ?? ''));
            $this->assertSame((string) $buyer->id, (string) ($payload['metadata']['buyer_id'] ?? ''));
            $this->assertSame('card', (string) ($payload['metadata']['payment_method'] ?? ''));
        });

        $response = $this->post($this->purchaseUrl($item->id), [
            'payment_method' => 'card',
        ]);

        $response->assertRedirect('https://example.com/stripe');
    }

    public function test_select_convenience_is_reflected_in_stripe_payload(): void
    {
        $this->disableBlockingMiddlewares();

        $buyer  = User::factory()->create(['email_verified_at' => now()]);
        $seller = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'is_sold' => false,
        ]);

        $this->actingAs($buyer);
        $this->setShippingSession($item->id);

        $this->mockStripeCreateSessionWithAssert(function ($payload) use ($item, $buyer) {
            $this->assertEquals(['konbini'], $payload['payment_method_types']);

            $this->assertSame((string) $item->id, (string) ($payload['metadata']['item_id'] ?? ''));
            $this->assertSame((string) $buyer->id, (string) ($payload['metadata']['buyer_id'] ?? ''));
            $this->assertSame('convenience', (string) ($payload['metadata']['payment_method'] ?? ''));
        });

        $response = $this->post($this->purchaseUrl($item->id), [
            'payment_method' => 'convenience',
        ]);

        $response->assertRedirect('https://example.com/stripe');
    }

    public function test_purchase_page_keeps_selected_payment_method_in_form(): void
    {
        $this->disableBlockingMiddlewares();

        $buyer  = User::factory()->create(['email_verified_at' => now()]);
        $seller = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'is_sold' => false,
        ]);

        $this->actingAs($buyer);

        $response = $this->withSession([
            '_old_input' => ['payment_method' => 'convenience'],
        ])->get($this->purchaseUrl($item->id));

        $response->assertStatus(200);
        $response->assertSee('value="convenience"', false);
    }
}