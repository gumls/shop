<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
        "payment/alipay/notify", //支付宝回调地址 不会有 CSRF Token
        "payment/wechat/notify", //微信支付回调
        'payment/wechat/refund_notify', //微信退款回调
    ];
}
