<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemIndexTest extends TestCase
{
    use RefreshDatabase;

    private string $indexUrl = '/';

    /**
     * 商品を購入済みにする
     */
    private function markAsSold(Item $item): void
    {
        $item->update([
            'is_sold' => true,
            'sold_at' => now(),
        ]);
    }

    /**
     * 全商品が表示される
     */
    public function test_all_items_are_displayed(): void
    {
        Item::factory()->create(['name' => '商品A']);
        Item::factory()->create(['name' => '商品B']);

        $response = $this->get($this->indexUrl);

        $response->assertOk();
        $response->assertSee('商品A');
        $response->assertSee('商品B');
    }

    /**
     * 購入済み商品は「Sold」と表示される
     */
    public function test_sold_item_shows_sold_label(): void
    {
        $sold = Item::factory()->create(['name' => '売れた商品']);
        $this->markAsSold($sold);

        $response = $this->get($this->indexUrl);

        $response->assertOk();
        $response->assertSee('売れた商品');
        $response->assertSee('Sold');
    }

    /**
     * ログインユーザーの出品商品は一覧に表示されない
     */
    public function test_my_own_items_are_not_displayed(): void
    {
        $me = User::factory()->create();
        $this->actingAs($me);

        Item::factory()->create([
            'name' => '自分の商品',
            'user_id' => $me->id,
        ]);

        Item::factory()->create([
            'name' => '他人の商品',
        ]);

        $response = $this->get($this->indexUrl);

        $response->assertOk();
        $response->assertDontSee('自分の商品');
        $response->assertSee('他人の商品');
    }
}