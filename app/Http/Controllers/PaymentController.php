<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yansongda\Pay\Pay;

class PaymentController extends Controller {

    //支付宝支付
    public function payByAlipay(Order $order,Request $request){
        $this->authorize("own",$order);
        if($order->paid_at || $order->closed){
            throw new InvalidRequestException("订单状态异常");
        }

        //调用支付宝网页支付
        return app("alipay")->web([
            "out_trade_no" => $order->no,
            "total_amount" => $order->total_amount,
            "subject"      => "支付订单:".$order->no,
        ]);

    }

    //前端回调
    public function alipayReturn(){
        try {
            $data = app("alipay")->verify();
        } catch (\Exception $e){
            return view("pages.error",["msg"=>"数据不正确"]);
        }
        $order = Order::where("no",$data->out_trade_no)->first();
        if(!$order){
            return view("pages.error",["msg"=>"订单不存在"]);
        }
        if($order->paid_at){
            app("alipay")->success();
            return view("pages.success",["msg"=>"付款成功"]);
        }
        $order->update([
            "paid_at" => Carbon::now(),
            "payment_method" => "alipay",
            "payment_no" => $data->trade_no,
        ]);
        app("alipay")->success();
        $this->afterPaid($order);
        return view("pages.success",["msg"=>"付款成功"]);
    }

    //服务器回调
    public function alipayNotify(){
        $data = app("alipay")->verify();
        // 如果订单状态不是成功或者结束，则不走后续的逻辑
        // 所有交易状态：https://docs.open.alipay.com/59/103672
        if(!in_array($data->trade_status,["TRADE_SUCCESS","TRADE_FINISHED"])){
            return app("alipay")->success();
        }
        $order = Order::where("no",$data->out_trade_no)->first();
        if(!$order){
            return "fail";
        }
        if($order->paid_at){
            return app("alipay")->success();
        }
        $order->update([
            "paid_at" => Carbon::now(),
            "payment_method" => "alipay",
            "payment_no" => $data->trade_no,
        ]);
        $this->afterPaid($order);
        return app("alipay")->success();
    }

    //微信支付
    public function payByWechat(Order $order,Request $request){
        $this->authorize("own",$order);
        if($order->paid_at || $order->closed){
            throw new InvalidRequestException("订单状态不正确");
        }
        //微信扫码支付
        return app("wechat_pay")->scan([
            "out_trade_no" => $order->no,
            "total_fee"    => $order->total_amount * 100, //微信支付 分为单位
            "body"         => "测试微信支付,订单号:".$order->no,
        ]);
    }
    //微信支付只有服务端会调
    public function wechatNotify(){
        $data = app("wechat_pay")->verify();
        $order = Order::where("no",$data->out_trade_no)->first();
        if(!$order){
            return "fail";
        }
        //已支付
        if($order->paid_at){
            return app("wechat_pay")->success();
        }

        $order->update([
            "paid_at" => Carbon::now(),
            "payment_method" => "wechat",
            "payment_no" => $data->transaction_id,
        ]);
        $this->afterPaid($order);
        return app("wechat_pay")->success();
    }

    //事件 支付之后的操作
    public function afterPaid(Order $order){
        event(new OrderPaid($order));
    }
}
