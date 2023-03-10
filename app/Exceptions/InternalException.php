<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Exception;

class InternalException extends Exception
{
    //
    public function __construct(string $message,string $msgForUser = "系统内部错误",int $code = 0)
    {
        parent::__construct($message, $code);
        $this->msgForUser = $msgForUser;
    }

    public function render(Request $request){
        if($request->expectsJson()){
            return response()->json(["msg"=>$this->msgForUser],$this->code);
        }
        return view("pages.err",["msg" => $this->msgForUser]);
    }
}
