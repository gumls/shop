<?php

namespace Database\Factories;

use App\Models\CouponCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponCodeFactory extends Factory
{
    protected $model = CouponCode::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        //取得一个随机的类型
        $type = $this->faker->randomElement(array_keys(CouponCode::$typeMap));
        //生成对应的折扣
        $value = $type === CouponCode::TYPE_FIXED?random_int(1,200):random_int(1,50);
        //固定金额 最低订单金额必须比折扣高0.01
        if($type === CouponCode::TYPE_FIXED){
            $minAmount = $value + 0.01;
        } else {
            //百分比 有50%几率不限制最低金额
            if(random_int(1,100) < 50){
                $minAmount = 0;
            } else {
                $minAmount = random_int(100,1000);
            }
        }
        return [
            //
            "name" => join(" ",$this->faker->words), //随机名称
            "code" => CouponCode::createCode(16),
            "type" => $type,
            "value"=> $value,
            "total"=> 1000,
            "used" => 0,
            "min_amount" => $minAmount,
            "not_before" => null,
            "not_after"  => null,
            "enabled"    => true,
        ];
    }
}
