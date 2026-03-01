<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    public function store(CommentRequest $request, int $item_id): RedirectResponse
    {
        $item = Item::findOrFail($item_id);

        $item->comments()->create([
            'user_id' => $request->user()->id,
            'body'    => $request->input('body'),
        ]);

        return redirect()->route('items.show', ['item_id' => $item_id]);
    }
}