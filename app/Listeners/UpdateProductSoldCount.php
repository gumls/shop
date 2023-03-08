<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\OrderItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

//ShouldQueue 代表是异步执行
class UpdateProductSoldCount implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\OrderPaid  $event
     * @return void
     */
    public function handle(OrderPaid $event)
    {
        //
        $order = $event->getOrder();
        //预加载商品数据
        $order->load("items.product");

        foreach ($order->items as $item){
            $product = $item->product;

            $soldCount = OrderItem::query()
                ->where("product_id",$product->id)
                ->whereHas("order",function ($query){
                    $query->whereNotNull("paid_at");
                })->sum("amount");

            $product->update([
                "sold_count" => $soldCount,
            ]);
        }
    }
}
