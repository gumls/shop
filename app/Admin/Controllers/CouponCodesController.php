<?php

namespace App\Admin\Controllers;

use App\Models\CouponCode;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CouponCodesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '优惠券';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CouponCode());
        //按创建时间倒序排列
        $grid->model()->orderBy("created_at","desc");
        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('名称'));
        $grid->column('code', __('优惠券码'));
        $grid->column('description', __('描述'));
        $grid->column('usage', __('描述'))->display(function ($v){
            return $this->used."/".$this->total;
        });
        $grid->column('type', __('类型'))->display(function ($v){
            return CouponCode::$typeMap[$v];
        });
        $grid->column('value', __('折扣'))->display(function ($v){
            return $this->type === CouponCode::TYPE_FIXED ? "¥".$v:$v."%";
        });
        $grid->column('total', __('总量'));
        $grid->column('used', __('已使用'));
        $grid->column('min_amount', __('最低金额'));
//        $grid->column('not_before', __('Not before'));
//        $grid->column('not_after', __('Not after'));
        $grid->column('enabled', __('是否启用'))->display(function ($v){
            return $v?"是":"否";
        });
        $grid->column('created_at', __('创建时间'));
//        $grid->column('updated_at', __('Updated at'));
        $grid->actions(function ($ac){
            $ac->disableView();
        });
        return $grid;
    }

    protected function form(){
        $form = new Form(new CouponCode);

        $form->display('id', 'ID');
        $form->text('name', '名称')->rules('required');
        $form->text('code', '优惠码')->rules('nullable|unique:coupon_codes');
        $form->radio('type', '类型')->options(CouponCode::$typeMap)->rules('required')->default(CouponCode::TYPE_FIXED);
        $form->text('value', '折扣')->rules(function ($form) {
            if (request()->input('type') === CouponCode::TYPE_PERCENT) {
                // 如果选择了百分比折扣类型，那么折扣范围只能是 1 ~ 99
                return 'required|numeric|between:1,99';
            } else {
                // 否则只要大等于 0.01 即可
                return 'required|numeric|min:0.01';
            }
        });
        $form->text('total', '总量')->rules('required|numeric|min:0');
        $form->text('min_amount', '最低金额')->rules('required|numeric|min:0');
        $form->datetime('not_before', '开始时间');
        $form->datetime('not_after', '结束时间');
        $form->radio('enabled', '启用')->options(['1' => '是', '0' => '否']);

        $form->saving(function (Form $form) {
            if (!$form->code) {
                $form->code = CouponCode::findAvailableCode();
            }
        });

        return $form;

    }
}
