<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $cartService;
    public function __construct(CartService $cartService){
        $this->cartService = $cartService;
    }
    //
    public function add(AddCartRequest $request){
        $skuId = $request->input("sku_id");
        $amount = $request->input("amount");
        $this->cartService->add($skuId,$amount);
        return [];
    }
    //查看购物车
    public function index(Request $request){
        $cartItem = $this->cartService->get();
        $addresses = $request->user()->addresses()->orderBy('last_used_at', 'desc')->get();
        return view("cart.index",["cartItems"=>$cartItem,"addresses"=>$addresses]);
    }
    //移除购物车
    public function remove(ProductSku $sku,Request $request){
        $this->cartService->remove($sku->id);
        return [];
    }
}
