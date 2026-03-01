<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_like_and_is_redirected_to_login(): void
    {
        $item = Item::factory()->create();

        $response = $this->post(route('items.like', ['item_id' => $item->id]));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_like_item(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->post(route('items.like', ['item_id' => $item->id]));

        $response->assertRedirect();

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    public function test_user_can_unlike_item_by_toggling_again(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $this->post(route('items.like', ['item_id' => $item->id]))->assertRedirect();

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->post(route('items.like', ['item_id' => $item->id]))->assertRedirect();

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    public function test_like_is_unique_per_user_and_item(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $this->post(route('items.like', ['item_id' => $item->id]))->assertRedirect();

        $this->assertDatabaseCount('likes', 1);
    }
}