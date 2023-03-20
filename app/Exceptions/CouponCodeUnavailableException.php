<?php

namespace App\Exceptions;

use App\Http\Requests\Request;
use Exception;

class CouponCodeUnavailableException extends Exception
{
    //
    public function __construct($message = "", int $code = 403)
    {
        parent::__construct($message, $code);
    }

    public function render(Request $request){
        //如果用户通过api请求 返回json数据
        if($request->expectsJson()){
            return response()->json(["msg"=>$this->message],$this->code);
        }
        //否则返回上一页带回错误信息
        return redirect()->back()->withErrors(["coupon_code"=>$this->message]);
    }
}
