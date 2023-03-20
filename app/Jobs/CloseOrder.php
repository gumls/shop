<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CloseOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $order;

    public function __construct(Order $order,$delay) {
        //
        $this->order = $order;
        //设置延时时间
        $this->delay($delay);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        //如果已经支付 退出
        if($this->order->paid_at){
            return;
        }
        DB::transaction(function (){
            $this->order->update(["closed"=>true]);
            foreach ($this->order->items as $item){
                $item->productSku->addStock($item->amount);
            }
            if($this->order->couponCode){
                $this->order->couponCode->changeUsed(false);
            }
        });
    }
}
