<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class Order extends Model
{
    use HasFactory;

    const REFUND_STATUS_PENDING = 'pending';
    const REFUND_STATUS_APPLIED = 'applied';
    const REFUND_STATUS_PROCESSING = 'processing';
    const REFUND_STATUS_SUCCESS = 'success';
    const REFUND_STATUS_FAILED = 'failed';

    const SHIP_STATUS_PENDING = 'pending';
    const SHIP_STATUS_DELIVERED = 'delivered';
    const SHIP_STATUS_RECEIVED = 'received';

    public static $refundStatusMap = [
        self::REFUND_STATUS_PENDING => "未退款",
        self::REFUND_STATUS_APPLIED => "已申请退款",
        self::REFUND_STATUS_PROCESSING => "退款中",
        self::REFUND_STATUS_SUCCESS => "退款成功",
        self::REFUND_STATUS_FAILED => "退款失败",
    ];

    public static $shipStatusMap = [
        self::SHIP_STATUS_PENDING => "未发货",
        self::SHIP_STATUS_DELIVERED => "已发货",
        self::SHIP_STATUS_RECEIVED => "已收货",
    ];

    protected $fillable = [
        'no',
        'address',
        'total_amount',
        'remark',
        'paid_at',
        'payment_method',
        'payment_no',
        'refund_status',
        'refund_no',
        'closed',
        'reviewed',
        'ship_status',
        'ship_data',
        'extra',
    ];

    protected $casts = [
        'closed'    => 'boolean',
        'reviewed'  => 'boolean',
        'address'   => 'json',
        'ship_data' => 'json',
        'extra'     => 'json',
    ];

    protected $dates = [
        "paid_at",
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function items(){
        return $this->hasMany(OrderItem::class);
    }
    protected static function boot(){
        parent::boot();
        static::creating(function ($model){
            if(!$model->no){
                $model->no = self::findAvailableNo();
                if(!$model->no){
                    return false;
                }
            }
        });
    }

    //生成订单号
    public static function findAvailableNo(){
        $prefix = date("YmdHis");
        for ($i=0;$i<10;$i++){
            //随机6位数字
            $no = $prefix.str_pad(random_int(0,999999),6,'0',STR_PAD_LEFT);
            if(!static::query()->where("no",$no)->exists()){
                return $no;
            }
        }
        Log::warning('订单号生成失败');
        return false;
    }

    //生成退款单号
    public static function getAvailableRefundNo(){
        do {
            $no = Uuid::uuid4()->getHex();
        } while(self::query()->where("refund_no",$no)->exists());

        return $no;
    }

    public function couponCode(){
        return $this->belongsTo(CouponCode::class);
    }
}
