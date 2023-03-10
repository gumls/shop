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
}
