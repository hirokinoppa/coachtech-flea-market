<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExhibitionRequest;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SellController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(): View
    {
        $categories = Category::query()->orderBy('id')->get();

        return view('items.sell', compact('categories'));
    }

    public function store(ExhibitionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $userId = (int) Auth::id();

        $path = null;

        try {
            return DB::transaction(function () use ($request, $validated, $userId, &$path) {

                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('items', 'public'); // storage/app/public/items
                }

                $item = Item::create([
                    'user_id'     => $userId,
                    'name'        => $validated['name'],
                    'brand'       => $validated['brand'] ?? null,
                    'description' => $validated['description'],
                    'image_path'  => $path, // null OK
                    'condition'   => $validated['condition'],
                    'price'       => (int) $validated['price'],
                    'is_sold'     => false,
                    'sold_at'     => null,
                ]);

                $item->categories()->sync($validated['category_ids']);

                return redirect()
                    ->route('items.index')
                    ->with('message', '商品を出品しました');
            });
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }
    }
}