<?php

namespace App\Models;

use App\Exceptions\CouponCodeUnavailableException;
use Carbon\Carbon;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CouponCode extends Model
{
    use HasFactory,DefaultDatetimeFormat;

    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENT = 'percent';

    public static $typeMap = [
        self::TYPE_FIXED => '固定金额',
        self::TYPE_PERCENT => '比例',
    ];

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'total',
        'used',
        'min_amount',
        'not_before',
        'not_after',
        'enabled',
    ];

    protected $casts = [
        "enabled" => "boolean",
    ];

    protected $dates = ["not_before","not_after"];

    protected $appends = ["description"];

    public static function createCode($len=16){
        do {
            $code = strtoupper(Str::random($len));
        } while (self::query()->where("code",$code)->exists());

        return $code;
    }

    public function getDescriptionAttribute(){
        $str = "";
        if($this->min_amount > 0){
            $str = "满".$this->min_amount;
        }
        if($this->type == self::TYPE_PERCENT){
            return $str."优惠".str_replace(".00","",$this->value)."%";
        }
        return $str."减".str_replace(".00","",$this->value);
    }

    //检查优惠券
    public function checkAvailable($orderAmount = null){
        if(!$this->enabled){
            throw new CouponCodeUnavailableException("优惠券不存在");
        }
        if($this->total - $this->used <= 0){
            throw new CouponCodeUnavailableException("优惠券已用完");
        }
        if($this->not_before && $this->not_before->gt(Carbon::now())){
            throw new CouponCodeUnavailableException("优惠券现在还不能使用");
        }
        if($this->not_after && $this->not_after->lt(Carbon::now())){
            throw new CouponCodeUnavailableException("优惠券已过期");
        }
        if(!is_null($orderAmount) && $orderAmount < $this->min_amount){
            throw new CouponCodeUnavailableException("订单金额不满足该优惠券最低金额");
        }

    }
    //计算优惠后的金额
    public function getAdjustedPrice($orderAmount){
        //固定金额
        if($this->type == self::TYPE_FIXED){
            return max(0.01,$orderAmount - $this->value);
        }
        return number_format($orderAmount * (100 - $this->value)/100,2,".","");
    }
    //减用量 或者 加用量
    public function changeUsed($increase = true){
        if($increase){
            return $this->query()->where("id",$this->id)->where("used","<",$this->total)->increment("used");
        } else {
            return $this->decrement("used");
        }
    }
}
