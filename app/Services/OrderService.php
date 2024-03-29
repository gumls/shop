<?php

namespace App\Services;

use App\Exceptions\CouponCodeUnavailableException;
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseOrder;
use App\Models\CouponCode;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\UserAddress;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrderService {
    //创建订单
    public function store(User $user,UserAddress $address,$remark,$items,CouponCode $coupon = null){
        //先检查是否可用
        if($coupon){
            $coupon->checkAvailable($user);
        }
        $order = DB::transaction(function ()use ($user,$address,$remark,$items,$coupon){
            // 更新此地址的最后使用时间
            $address->update([
                "last_used_at" => Carbon::now(),
            ]);
            $order = new Order([
                'address'      => [ // 将地址信息放入订单中
                    'address'       => $address->full_address,
                    'zip'           => $address->zip,
                    'contact_name'  => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark'       => $remark,
                'total_amount' => 0,
            ]);
            //关联用户
            $order->user()->associate($user);
            //保存
            $order->save();

            $totalAmount = 0;
            // 遍历用户提交的 SKU
            foreach ($items as $data) {
                $sku  = ProductSku::find($data['sku_id']);
                // 创建一个 OrderItem 并直接与当前订单关联
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price'  => $sku->price,
                ]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();
                $totalAmount += $sku->price * $data['amount'];
                if ($sku->decreaseStock($data['amount']) <= 0) {
                    throw new InvalidRequestException('该商品库存不足');
                }
            }
            if($coupon){
                //再次检车优惠券
                $coupon->checkAvailable($user,$totalAmount);
                //使用优惠券之后的金额
                $totalAmount = $coupon->getAdjustedPrice($totalAmount);
                //订单与优惠券关联
                $order->couponCode()->associate($coupon);
                //减优惠券
                if($coupon->changeUsed(true) <= 0){
                    throw new CouponCodeUnavailableException("该优惠券已被兑完");
                }
            }
            // 更新订单总金额
            $order->update(['total_amount' => $totalAmount]);

            // 将下单的商品从购物车中移除
            $skuIds = collect($items)->pluck('sku_id')->all();
            app(CartService::class)->remove($skuIds);

            return $order;
        });

        //定时任务
        dispatch(new CloseOrder($order,config("app.order_ttl")));

        return $order;
    }
}
