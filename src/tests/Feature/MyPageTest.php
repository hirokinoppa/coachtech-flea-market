<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Http\Middleware\EnsureProfileIsComplete;

class MyPageTest extends TestCase
{
    use RefreshDatabase;

    private string $myPageUrl = '/mypage';

    private function disableMyPageMiddlewares(): void
    {
        $this->withoutMiddleware([
            EnsureEmailIsVerified::class,
            EnsureProfileIsComplete::class,
        ]);
    }

    public function test_guest_is_redirected_from_mypage(): void
    {
        $response = $this->get($this->myPageUrl);

        $response->assertRedirect('/login');
    }

    public function test_mypage_shows_user_name_listed_items_and_purchased_items(): void
    {
        $this->disableMyPageMiddlewares();

        $me = User::factory()->create([
            'name' => 'テスト太郎',
            'email_verified_at' => now(),
        ]);
        $this->actingAs($me);

        Item::factory()->create([
            'user_id' => $me->id,
            'name' => '出品商品A',
        ]);

        Item::factory()->create([
            'user_id' => $me->id,
            'name' => '出品商品B',
        ]);

        $seller = User::factory()->create();

        $purchasedItem = Item::factory()->create([
            'user_id' => $seller->id,
            'name' => '購入商品X',
            'is_sold' => true,
        ]);

        DB::table('orders')->insert([
            'item_id' => $purchasedItem->id,
            'buyer_id' => $me->id,
            'seller_id' => $seller->id,
            'price' => (int) ($purchasedItem->price ?? 1000),
            'payment_method' => 2,
            'status' => 1,
            'shipping_postal_code' => '123-4567',
            'shipping_address' => '東京都テスト1-2-3',
            'shipping_building' => 'テストビル101',
            'purchased_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get($this->myPageUrl);

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
        $response->assertSee('出品商品A');
        $response->assertSee('出品商品B');
        $response->assertSee('購入商品X');
    }
}