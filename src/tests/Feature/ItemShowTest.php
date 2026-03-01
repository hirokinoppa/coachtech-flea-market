<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_detail_shows_required_information(): void
    {
        $seller = User::factory()->create();

        $item = Item::factory()->create([
            'user_id'     => $seller->id,
            'name'        => 'テスト商品',
            'brand'       => 'テストブランド',
            'description' => '説明です',
            'price'       => 12345,
            'condition'   => 'good',
            'is_sold'     => false,
        ]);

        $response = $this->get('/item/' . $item->id);

        $response->assertStatus(200);

        $response->assertSee('テスト商品');
        $response->assertSee('テストブランド');
        $response->assertSee('説明です');

        $html = $response->getContent();

        $raw = '12345';
        $formatted = number_format(12345);

        $this->assertTrue(
            str_contains($html, $raw) ||
            str_contains($html, $formatted) ||
            str_contains($html, '¥' . $formatted) ||
            str_contains($html, '&yen;' . $formatted),
            "価格の表示が想定と違います。raw={$raw}, formatted={$formatted}"
        );
    }

    public function test_item_detail_shows_multiple_categories(): void
    {
        $seller = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'name'    => 'カテゴリ確認商品',
        ]);

        $cat1 = \App\Models\Category::factory()->create([
            'name' => 'キッチン'
        ]);

        $cat2 = \App\Models\Category::factory()->create([
            'name' => '家電'
        ]);

        $item->categories()->attach([
            $cat1->id,
            $cat2->id
        ]);

        $response = $this->get('/item/' . $item->id);

        $response->assertStatus(200);
        $response->assertSee('キッチン');
        $response->assertSee('家電');
    }
}