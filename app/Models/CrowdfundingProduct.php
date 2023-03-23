<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrowdfundingProduct extends Model
{
    use HasFactory;

    //众筹的三种状态
    const STATUS_FUNDING = "funding";
    const STATUS_SUCCESS = "success";
    const STATUS_FAIL = "fail";

    public static $statuMap = [
        self::STATUS_FUNDING => "众筹中",
        self::STATUS_SUCCESS => "众筹成功",
        self::STATUS_FAIL => "众筹失败",
    ];

    protected $fillable = ["target_amount","total_amount","user_count","end_at","status"];
    protected $dates = ["end_at"];
    public $timestamps = false;

    //关联商品
    public function products(){
        return $this->belongsTo(Product::class);
    }

    //定义进度访问器
    public function getPercentAttribute(){
        $value = $this->attributes["total_amount"] / $this->attributes["target_amount"];
        return floatval(number_format($value*100,2,".",""));
    }
}
