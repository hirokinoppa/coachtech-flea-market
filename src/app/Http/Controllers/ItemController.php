<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab');
        $keyword = trim((string) $request->query('keyword', ''));

        $query = Item::query()
            ->with('categories')
            ->when(Auth::check(), function ($q) {
                $q->where('user_id', '!=', Auth::id());
            })
            ->when($keyword !== '', function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%');
            });

        if ($tab === 'mylist') {
            if (!Auth::check()) {
                $items = collect();
                return view('items.index', compact('items', 'tab', 'keyword'));
            }

            $query->whereHas('likes', function ($q) {
                $q->where('user_id', Auth::id());
            })->orderByDesc('created_at');
        } else {
            $query->orderBy('is_sold', 'asc')
                ->orderByDesc('created_at');
        }

        $items = $query->get();

        return view('items.index', compact('items', 'tab', 'keyword'));
    }

    public function show(int $item_id): View
    {
        $item = Item::query()
            ->with([
                'user',
                'categories',
                'comments.user.profile',
            ])
            ->withCount([
                'likes',
                'comments',
            ])
            ->findOrFail($item_id);

        $isLiked = Auth::check()
            ? $item->likes()->where('user_id', Auth::id())->exists()
            : false;

        $canPurchase = Auth::check()
            && !$item->is_sold
            && $item->user_id !== Auth::id();

        return view('items.show', compact('item', 'isLiked', 'canPurchase'));
    }

    public function toggleLike(int $item_id): RedirectResponse
    {
        $item = Item::findOrFail($item_id);

        $likeQuery = $item->likes()->where('user_id', Auth::id());

        if ($likeQuery->exists()) {
            $likeQuery->delete();
        } else {
            $item->likes()->create([
                'user_id' => Auth::id(),
            ]);
        }

        return back();
    }
}