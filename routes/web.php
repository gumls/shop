<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::redirect("/","/products")->name("root");
//Route::get('/', 'PagesController@root')->name('root');
Auth::routes(["verify"=>true]);
Route::get("/products/favorites","ProductsController@favorites")->name("products.favorites");
Route::group(["middleware"=>["auth","verified"]],function (){
    Route::get("user_addresses","UserAddressesController@index")->name("user_addresses.index");
    Route::get("user_addresses/create","UserAddressesController@create")->name("user_addresses.create");
    Route::post("user_addresses/store","UserAddressesController@store")->name("user_addresses.store");
    Route::get("user_addresses/{user_address}","UserAddressesController@edit")->name("user_addresses.edit");
    Route::put("user_addresses/{user_address}","UserAddressesController@update")->name("user_addresses.update");
    Route::delete("user_addresses/{user_address}","UserAddressesController@destroy")->name("user_addresses.destroy");
    Route::post("/products/{product}/favorite","ProductsController@favor")->name("products.favor");
    Route::delete("/products/{product}/favorite","ProductsController@disFavor")->name("products.disfavor");
    Route::post("cart","CartController@add")->name("cart.add");
    Route::get("cart","CartController@index")->name("cart.index");
    Route::delete("cart/{sku}","CartController@remove")->name("cart.remove");
    Route::post("orders","OrderController@store")->name("orders.store");
    Route::get("orders","OrderController@index")->name("orders.index");
    Route::get("orders/{order}","OrderController@show")->name("orders.show");
    Route::post("orders/{order}/received","OrderController@received")->name("orders.received");
    Route::get('payment/{order}/alipay', 'PaymentController@payByAlipay')->name('payment.alipay');
    Route::get('payment/alipay/return', 'PaymentController@alipayReturn')->name('payment.alipay.return');
    Route::get('payment/{order}/wechat', 'PaymentController@payByWechat')->name('payment.wechat');
    Route::get('orders/{order}/review', 'OrderController@review')->name('orders.review.show');
    Route::post('orders/{order}/review', 'OrderController@sendReview')->name('orders.review.store');
    Route::post('orders/{order}/apply_refund', 'OrderController@applyRefund')->name('orders.apply_refund');
});

//服务器端回调的路由不能放到带有 auth 中间件的路由组中，因为支付宝的服务器请求不会带有认证信息
Route::post('payment/alipay/notify', 'PaymentController@alipayNotify')->name('payment.alipay.notify');
Route::post('payment/wechat/notify', 'PaymentController@wechatNotify')->name('payment.wechat.notify');

Route::get("/products","ProductsController@index")->name("products.index");
Route::get("/products/{product}","ProductsController@show")->name("products.show");

