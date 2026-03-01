<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\AddressRequest;
use Illuminate\Http\Request;
use App\Models\Item;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    private function shippingSessionKey(int $itemId): string
    {
        return "purchase.shipping.{$itemId}";
    }

    private function getShippingForItem(int $itemId): array
    {
        $user = Auth::user();
        $profile = $user?->profile;

        $default = [
            'postal_code' => $profile->postal_code ?? '',
            'address'     => $profile->address ?? '',
            'building'    => $profile->building ?? '',
        ];

        $shipping = session($this->shippingSessionKey($itemId), []);
        return array_merge($default, is_array($shipping) ? $shipping : []);
    }

    public function index(int $item_id): View
    {
        $item = Item::findOrFail($item_id);
        $shipping = $this->getShippingForItem($item_id);

        return view('items.purchase', compact('item', 'shipping'));
    }

    public function editAddress(int $item_id): View
    {
        $item = Item::findOrFail($item_id);
        $shipping = $this->getShippingForItem($item_id);

        return view('items.address', compact('item', 'shipping'));
    }

    public function updateAddress(AddressRequest $request, int $item_id): RedirectResponse
    {
        Item::findOrFail($item_id);

        $data = $request->validated();

        session()->put($this->shippingSessionKey($item_id), [
            'postal_code' => $data['postal_code'],
            'address'     => $data['address'],
            'building'    => $data['building'] ?? null,
        ]);

        return redirect()
            ->route('purchase.show', ['item_id' => $item_id])
            ->with('message', '配送先を変更しました（プロフィールは変更していません）');
    }

    public function store(PurchaseRequest $request, int $item_id): RedirectResponse
    {
        $user = $request->user();
        $item = Item::findOrFail($item_id);

        $paymentMethod = $request->input('payment_method');
        $paymentMethodTypes = $paymentMethod === 'convenience'
            ? ['konbini']
            : ['card'];

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = CheckoutSession::create([
            'mode' => 'payment',
            'payment_method_types' => $paymentMethodTypes,
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'jpy',
                    'unit_amount' => (int) $item->price,
                    'product_data' => [
                        'name' => $item->name,
                    ],
                ],
            ]],
            'metadata' => [
                'item_id' => $item->id,
                'buyer_id' => $user->id,
                'payment_method' => $paymentMethod,
            ],
            'success_url' => route('purchase.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('purchase.show', ['item_id' => $item->id]),
        ]);

        return redirect()->away($session->url);
    }

    public function success(Request $request): RedirectResponse
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return redirect()->route('items.index');
        }

        $authUser = $request->user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        $session = CheckoutSession::retrieve($sessionId);

        if (($session->payment_status ?? null) !== 'paid') {
            return redirect()
                ->route('items.index')
                ->with('message', '決済の確認中です（コンビニ払いは反映に時間がかかる場合があります）。');
        }

        $itemId  = (int) ($session->metadata->item_id ?? 0);
        $buyerId = (int) ($session->metadata->buyer_id ?? 0);
        $paymentMethod = (string) ($session->metadata->payment_method ?? '');

        if ($buyerId !== (int) $authUser->id) {
            return redirect()->route('items.index');
        }

        return DB::transaction(function () use ($itemId, $authUser, $paymentMethod) {

            $item = Item::query()->lockForUpdate()->findOrFail($itemId);

            if ((bool) $item->is_sold) {
                return redirect()->route('items.index');
            }

            $shipping = $this->getShippingForItem($item->id);

            if (empty($shipping['postal_code']) || empty($shipping['address'])) {
                return redirect()
                    ->route('purchase.show', ['item_id' => $item->id])
                    ->withErrors(['shipping' => '配送先が未設定です。配送先を設定してください。']);
            }

            $paymentMethodValue = $paymentMethod === 'convenience' ? 1 : 2;

            Order::create([
                'item_id'              => $item->id,
                'buyer_id'             => $authUser->id,
                'seller_id'            => $item->user_id,
                'price'                => (int) $item->price,
                'payment_method'       => $paymentMethodValue,
                'status'               => 1,
                'shipping_postal_code' => $shipping['postal_code'],
                'shipping_address'     => $shipping['address'],
                'shipping_building'    => $shipping['building'] ?: null,
                'purchased_at'         => now(),
            ]);

            $item->is_sold = true;
            $item->sold_at = now();
            $item->save();

            session()->forget($this->shippingSessionKey($item->id));

            return redirect()->route('items.index');
        });
    }
}