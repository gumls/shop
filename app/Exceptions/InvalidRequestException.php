<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Exception;

class InvalidRequestException extends Exception {
    //
    public  function __construct(string $message = "", int $code = 400) {
        parent::__construct($message, $code);
    }
    public function render(Request $request){
        if($request->expectsJson()){
            return response()->json(["msg"=>$this->message],$this->code);
        }
        return view("pages.error",["msg"=>$this->message]);
    }
}
