<?php

namespace App\Providers;

use App\Http\ViewComposers\CategoryTreeComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Yansongda\Pay\Pay;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //注入支付 alipay
        $this->app->singleton("alipay",function (){
            $config = config("pay.alipay");
            //服务端回调
            $config["notify_url"] = route("payment.alipay.notify");
            $config["return_url"] = route("payment.alipay.return");
            //判断是否为线上环境
            if(app()->environment() !== "production"){
                $config["mode"] = "dev";
                $config["log"]["level"] = Logger::DEBUG;
            } else {
                $config["log"]["level"] = Logger::WARNING;
            }
            //调用支付
            return Pay::alipay($config);
        });

        //注入支付 wechatpay
        $this->app->singleton("wechat_pay",function (){
            $config = config("pay.wechat");
            $config["notify_url"] = ngrok_url("payment.wechat.notify");
            //判断是否为线上环境
            if(app()->environment() !== "production"){
                $config["log"]["level"] = Logger::DEBUG;
            } else {
                $config["log"]["level"] = Logger::WARNING;
            }
            //调用支付
            return Pay::wechat($config);
        });

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();
        View::composer(["products.index","products.show"],CategoryTreeComposer::class);
    }
}
