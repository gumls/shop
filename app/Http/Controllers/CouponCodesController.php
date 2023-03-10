<?php

namespace App\Http\Controllers;

use App\Models\CouponCode;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CouponCodesController extends Controller
{
    //
    public function show($code){
        if(!$record = CouponCode::query()->where("code",$code)->first()){
            abort(404);
        }
        if(!$record->enabled){
            abort(404);
        }
        if($record->total - $record->used <= 0){
            return response()->json(["msg"=>"优惠券已兑完"],403);
        }
        if($record->not_before && $record->not_before->gt(Carbon::now())){
            return response()->json(["msg"=>"优惠券现在还不能使用"],403);
        }
        if($record->not_after && $record->not_after->lt(Carbon::now())){
            return response()->json(["msg"=>"优惠券已过期"],403);
        }

        return $record;
    }
}
