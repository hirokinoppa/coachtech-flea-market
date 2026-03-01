<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MylistIndexTest extends TestCase
{
    use RefreshDatabase;

    private function mylistUrlCandidates(): array
    {
        return [
            '/?tab=mylist',
            '/?type=mylist',
            '/?view=mylist',
            '/',
        ];
    }

    private function like(User $user, Item $item): void
    {
        DB::table('likes')->insert([
            'user_id'    => $user->id,
            'item_id'    => $item->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function getMylistResponse()
    {
        $last = null;

        foreach ($this->mylistUrlCandidates() as $url) {
            $res = $this->get($url);
            $last = $res;

            if ($res->status() === 200) {
                return $res;
            }
        }

        $status = $last ? $last->status() : 'no response';

        $this->fail(
            "マイリスト表示に使えるURLが見つかりませんでした。最後のstatus={$status}\n" .
            "候補: " . implode(', ', $this->mylistUrlCandidates()) . "\n" .
            "実装側(ItemController@index)でタブ判定に使っているクエリ名(tab/type/view等)を確認して、候補を合わせてください。"
        );
    }

    public function test_only_liked_items_are_displayed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $likedA   = Item::factory()->create(['name' => 'いいね商品A']);
        $notLiked = Item::factory()->create(['name' => 'いいねしてない商品']);
        $likedB   = Item::factory()->create(['name' => 'いいね商品B']);

        $this->like($user, $likedA);
        $this->like($user, $likedB);

        $response = $this->getMylistResponse();

        $response->assertSee('いいね商品A');
        $response->assertSee('いいね商品B');
        $response->assertDontSee('いいねしてない商品');
    }

    public function test_sold_liked_item_shows_sold_label(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $sold = Item::factory()->create([
            'name'    => '購入済みのいいね商品',
            'is_sold' => true,
        ]);

        $this->like($user, $sold);

        $response = $this->getMylistResponse();

        $response->assertSee('購入済みのいいね商品');
        $response->assertSee('Sold');
    }

    public function test_guest_sees_nothing_or_is_redirected(): void
    {
        Item::factory()->create(['name' => 'ゲストに見せたくない商品']);

        $response = $this->get('/?tab=mylist');

        if ($response->isRedirect()) {
            $response->assertRedirect();
            return;
        }

        $response->assertStatus(200);
        $response->assertDontSee('ゲストに見せたくない商品');
    }
}