<?php

namespace App\Http\Controllers;

use App\Events\OrderReviewed;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\ApplyRefundRequest;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\SendReviewRequest;
use App\Jobs\CloseOrder;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\UserAddress;
use App\Services\CartService;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller {
    //
    public function store(OrderRequest $request,OrderService $orderService){
        $user = $request->user();
        $address = UserAddress::find($request->input("address_id"));
        $remark = $request->input("remark");
        $items = $request->input("items");
        return $orderService->store($user,$address,$remark,$items);
    }

    //订单列表
    public function index(Request $request){
        $orders = Order::query()
            ->with(["items.product","items.productSku"])
            ->where("user_id",$request->user()->id)
            ->orderBy("created_at","desc")
            ->paginate();
        return view("orders.index",["orders"=>$orders]);
    }

    //订单详情页
    public function show(Order $order,Request $request){
        //校验权限 不是自己的订单不可查看
        $this->authorize('own',$order);
        return view("orders.show",["order"=>$order->load(["items.product","items.productSku"])]);
    }

    //确认收货
    public function received(Order $order,Request $request){
        $this->authorize("own",$order);
        //判断是否已发货
        if($order->ship_status !== Order::SHIP_STATUS_DELIVERED){
            throw new InvalidRequestException("发货状态不正确");
        }

        $order->update([
            "ship_status" => Order::SHIP_STATUS_RECEIVED,
        ]);

        return redirect()->back();
    }

    //评价
    public function review(Order $order){
        $this->authorize("own",$order);
        if(!$order->paid_at){
            throw new InvalidRequestException("订单未支付，不可评价");
        }
        if($order->reviewed){
            throw new InvalidRequestException("订单已评价");
        }
        //使用load关联加载数据
        return view("orders.review",["order"=>$order->load(["items.productSku","items.product"])]);
    }
    public function sendReview(Order $order,SendReviewRequest $request){
        $this->authorize("own",$order);
        if(!$order->paid_at){
            throw new InvalidRequestException("订单未支付，不可评价");
        }
        if($order->reviewed){
            throw new InvalidRequestException("订单已评价");
        }
        $reviews = $request->input("reviews");
        DB::transaction(function ()use ($order,$reviews){
            foreach ($reviews as $review){
                $orderItem = $order->items()->find($review["id"]);
                $orderItem->update([
                    "rating" => $review["rating"],
                    "review" => $review["review"],
                    "reviewed_at" => Carbon::now(),
                ]);
            }
            //订单标记为已评价
            $order->update(["reviewed"=>true]);
        });
        event(new OrderReviewed($order));
        return redirect()->back();
    }
    //申请退款
    public function applyRefund(Order $order,ApplyRefundRequest $request){
        $this->authorize("own",$order);
        if(!$order->paid_at){
            throw new InvalidRequestException("订单未付款");
        }
        if($order->refund_status !== Order::REFUND_STATUS_PENDING){
            throw new InvalidRequestException("订单已申请退款");
        }
        $extra = $order->extra ?:[];
        $extra["refund_reason"] = $request->input("reason");

        $order->update([
            "refund_status" => Order::REFUND_STATUS_APPLIED,
            "extra"         => $extra,
        ]);

        return $order;
    }
}
