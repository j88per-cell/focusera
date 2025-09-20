<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Photo;
use App\Support\Pricing;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected function currentCart(Request $request): Cart
    {
        $user = $request->user();
        if ($user) {
            $cart = Cart::firstOrCreate(['user_id' => $user->id], ['currency' => 'USD']);
        } else {
            $sid = $request->session()->getId();
            $cart = Cart::firstOrCreate(['session_id' => $sid], ['currency' => 'USD']);
        }
        return $cart->load('items.photo');
    }

    public function index(Request $request)
    {
        $cart = $this->currentCart($request);
        return response()->json($cart);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'photo_id' => 'required|exists:photos,id',
            'product_code' => 'nullable|string',
            'variant' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1',
            'unit_price_base' => 'nullable|numeric|min:0',
        ]);
        $qty = (int) ($data['quantity'] ?? 1);
        $photo = Photo::findOrFail($data['photo_id']);
        $cart = $this->currentCart($request);

        $percent = Pricing::resolveMarkupPercent($photo, $photo->gallery);
        $base = (float) ($data['unit_price_base'] ?? 0.0);
        $final = Pricing::applyMarkup($base, $percent);

        $item = $cart->items()->create([
            'photo_id' => $photo->id,
            'product_code' => $data['product_code'] ?? null,
            'variant' => $data['variant'] ?? null,
            'quantity' => $qty,
            'unit_price_base' => $base,
            'unit_price_final' => $final,
            'markup_percent' => $percent,
            'data' => null,
        ]);

        return response()->json($item->fresh('photo'), 201);
    }

    public function updateItem(Request $request, CartItem $item)
    {
        // Ensure the item belongs to this session/user cart
        $cart = $this->currentCart($request);
        if ($item->cart_id !== $cart->id) {
            abort(404);
        }
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        $item->update(['quantity' => $data['quantity']]);
        return response()->json($item);
    }

    public function removeItem(Request $request, CartItem $item)
    {
        $cart = $this->currentCart($request);
        if ($item->cart_id !== $cart->id) {
            abort(404);
        }
        $item->delete();
        return response()->noContent();
    }

    public function clear(Request $request)
    {
        $cart = $this->currentCart($request);
        $cart->items()->delete();
        return response()->noContent();
    }
}
