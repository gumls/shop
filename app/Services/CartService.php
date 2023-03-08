<?php
namespace App\Services;

use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

class CartService {
    //查看购物车
    public function get(){
        return Auth::user()->cartItems()->with("productSku.product")->get();
    }

    //新增购物车
    public function add($skuId,$amount){
        //获取user
        $user = Auth::user();
        if($item = $user->cartItems()->where("product_sku_id",$skuId)->first()){
            $item->update([
                "amount" => $item->amount + $amount,
            ]);
        } else {
            $item = new CartItem(["amount"=>$amount]);
            $item->user()->associate($user);
            $item->productSku()->associate($skuId);
            $item->save();
        }
        return $item;
    }

    //删除
    public function remove($skuIds){
        if(!is_array($skuIds)){
            $skuIds = [$skuIds];
        }
        Auth::user()->cartItems()->whereIn("product_sku_id",$skuIds)->delete();
    }
}
