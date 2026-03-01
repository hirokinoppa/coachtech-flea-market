<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Http\Middleware\EnsureProfileIsComplete;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemSearchTest extends TestCase
{
    use RefreshDatabase;

    private string $indexUrl = '/';
    private string $queryKey = 'keyword';

    public function test_can_search_items_by_partial_name(): void
    {
        Item::factory()->create(['name' => '青いスニーカー']);
        Item::factory()->create(['name' => '赤いTシャツ']);
        Item::factory()->create(['name' => '青い帽子']);

        $response = $this->get($this->indexUrl . '?' . http_build_query([
            $this->queryKey => '青い',
        ]));

        $response->assertStatus(200);
        $response->assertSee('青いスニーカー');
        $response->assertSee('青い帽子');
        $response->assertDontSee('赤いTシャツ');
    }

    /**
     * 検索状態がマイリストでも保持されている
     */
    public function test_search_keyword_is_kept_when_moving_to_mylist(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $this->withoutMiddleware([
            EnsureEmailIsVerified::class,
            EnsureProfileIsComplete::class,
        ]);

        $mylistUrl = '/mypage';
        $keyword = 'フライパン';

        $r1 = $this->get($this->indexUrl . '?' . http_build_query([
            $this->queryKey => $keyword,
        ]));

        $r1->assertStatus(200);

        $response2 = $this->get($mylistUrl . '?' . http_build_query([
            $this->queryKey => $keyword,
        ]));

        if ($response2->isRedirect()) {
            $location = $response2->headers->get('Location') ?? '';
            $this->fail("302 redirect happened. Location={$location}");
        }

        $response2->assertStatus(200);
        $response2->assertSee('value="' . e($keyword) . '"', false);
    }
}