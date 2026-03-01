<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentStoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * コメントPOSTのURL
     */
    private function commentUrl(Item $item): string
    {
        return "/item/{$item->id}/comment";
    }

    /**
     * 商品詳細ページURL
     */
    private function itemShowUrl(Item $item): string
    {
        return "/item/{$item->id}";
    }

    /**
     * バリデーションエラー判定
     * Webフォーム: 302 + errors
     * API(JSON): 422
     */
    private function assertValidationError($response, array $keys, string $redirectTo): void
    {
        if ($response->isRedirect()) {
            $response->assertRedirect($redirectTo);
            $response->assertSessionHasErrors($keys);
            return;
        }

        $response->assertStatus(422);
        $response->assertJsonValidationErrors($keys);
    }

    /**
     * ログインユーザーはコメントを送信できる
     */
    public function test_logged_in_user_can_send_comment(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user);

        $before = Comment::count();

        $response = $this->from($this->itemShowUrl($item))
            ->post($this->commentUrl($item), [
                'body' => 'テストコメント',
            ]);

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'body'    => 'テストコメント',
        ]);

        $this->assertSame($before + 1, Comment::count());

        $response->assertRedirect();
    }

    /**
     * 未ログインはコメント送信できない
     */
    public function test_guest_cannot_send_comment(): void
    {
        $item = Item::factory()->create();

        $response = $this->post($this->commentUrl($item), [
            'body' => 'ゲストコメント',
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
            'body'    => 'ゲストコメント',
        ]);
    }

    /**
     * body 必須バリデーション
     */
    public function test_comment_body_is_required(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user);

        $redirectTo = $this->itemShowUrl($item);

        $response = $this->from($redirectTo)
            ->post($this->commentUrl($item), [
                'body' => '',
            ]);

        $this->assertValidationError($response, ['body'], $redirectTo);
    }

    /**
     * body は255文字以内
     */
    public function test_comment_body_must_be_within_255_characters(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user);

        $redirectTo = $this->itemShowUrl($item);

        $response = $this->from($redirectTo)
            ->post($this->commentUrl($item), [
                'body' => str_repeat('a', 256),
            ]);

        $this->assertValidationError($response, ['body'], $redirectTo);
    }
}