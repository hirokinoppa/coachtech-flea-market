<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureProfileIsComplete;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellStoreTest extends TestCase
{
    use RefreshDatabase;

    private string $sellStoreUrl = '/sell';

    private function validPayload(array $override = []): array
    {
        return array_merge([
            'category_ids' => [],
            'condition' => 'good',
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'テスト説明です',
            'price' => '1234',
        ], $override);
    }

    public function test_user_can_store_item(): void
    {
        $this->withoutExceptionHandling();

        $this->withoutMiddleware([
            EnsureEmailIsVerified::class,
            EnsureProfileIsComplete::class,
        ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        $cat1 = Category::query()->create(['name' => '家電']);
        $cat2 = Category::query()->create(['name' => 'キッチン']);

        Storage::fake('public');
        $image = UploadedFile::fake()->create('item.jpg', 100, 'image/jpeg');

        $payload = $this->validPayload([
            'category_ids' => [$cat1->id, $cat2->id],
            'image' => $image,
        ]);

        $response = $this->from('/sell')->post($this->sellStoreUrl, $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('items', [
            'user_id' => $user->id,
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'テスト説明です',
            'condition' => 'good',
            'price' => 1234,
        ]);

        $item = Item::where('user_id', $user->id)
            ->where('name', 'テスト商品')
            ->firstOrFail();

        $this->assertDatabaseHas('category_item', [
            'item_id' => $item->id,
            'category_id' => $cat1->id,
        ]);

        $this->assertDatabaseHas('category_item', [
            'item_id' => $item->id,
            'category_id' => $cat2->id,
        ]);
    }

    public function test_guest_cannot_store_item(): void
    {
        $response = $this->post($this->sellStoreUrl, $this->validPayload());

        $response->assertRedirect();
        $this->assertStringEndsWith('/login', $response->headers->get('Location') ?? '');
    }

    public function test_sell_validation_required(): void
    {
        $this->withoutMiddleware([
            EnsureEmailIsVerified::class,
            EnsureProfileIsComplete::class,
        ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        $response = $this->from('/sell')->post($this->sellStoreUrl, $this->validPayload([
            'category_ids' => [],
            'condition' => '',
            'name' => '',
            'description' => '',
            'price' => '',
        ]));

        $response->assertRedirect('/sell');

        $response->assertSessionHasErrors([
            'condition',
            'name',
            'description',
            'price',
        ]);

        $errors = session('errors');
        $this->assertNotNull($errors);

        $this->assertTrue(
            $errors->has('category_ids') || $errors->has('category_ids.0'),
            'category_ids にエラーが付いていません'
        );
    }
}