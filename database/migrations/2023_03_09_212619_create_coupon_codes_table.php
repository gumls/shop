<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_codes', function (Blueprint $table) {
            $table->bigIncrements("id")->comment("自增id");
            $table->string('name')->comment("优惠券名称");
            $table->string('code')->unique()->comment("优惠券码");
            $table->string('type')->comment("类型，固定和百分比");
            $table->decimal('value')->comment("值");
            $table->unsignedInteger('total')->comment("可使用优惠券总数");
            $table->unsignedInteger('used')->default(0)->comment("已经领用");
            $table->decimal('min_amount', 10, 2)->comment("可使用最低金额");
            $table->datetime('not_before')->nullable()->comment("此时间之前不可用");
            $table->datetime('not_after')->nullable()->comment("此时间之后不可用");
            $table->boolean('enabled')->comment("是否生效");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupon_codes');
    }
}
