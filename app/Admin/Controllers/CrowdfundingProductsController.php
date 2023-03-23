<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\CrowdfundingProduct;
use App\Models\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CrowdfundingProductsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Product';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product());
        //只展示众筹商品
        $grid->model()->where("type",Product::TYPE_CROWDFUNDING);
        $grid->column('id', __('ID'))->sortable();
        $grid->column('title', __('商品名称'));
        $grid->column('on_sale', __('已上架'))->display(function ($v){
            return $v ? "是" : "否";
        });
        $grid->column('price', __('价格'));
        $grid->column('crowdfunding.target_amount', __('目标金额'));
        $grid->column('crowdfunding.total_amount', __('目前金额'));
        $grid->column('crowdfunding.end_at', __('结束时间'));
        $grid->column('crowdfunding.status', __('状态'))->display(function ($v){
            return CrowdfundingProduct::$statuMap[$v];
        });

        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });
        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Product::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('type', __('Type'));
        $show->field('category_id', __('Category id'));
        $show->field('title', __('Title'));
        $show->field('description', __('Description'));
        $show->field('image', __('Image'));
        $show->field('on_sale', __('On sale'));
        $show->field('rating', __('Rating'));
        $show->field('sold_count', __('Sold count'));
        $show->field('review_count', __('Review count'));
        $show->field('price', __('Price'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Product());

        //隐藏type 值为众筹类型
        $form->hidden("type")->value(Product::TYPE_CROWDFUNDING);
        $form->text("titile","商品名称")->rules("required");
        $form->select("category_id","类目")->options(function ($id){
            $category = Category::find($id);
            if($category){
                return [$category->id => $category->full_name];
            }
        })->ajax("/admin/api/categories?is_directory=0");
        //封面图
        $form->image("image","封面图")->rules("required|image");
        $form->quill("description","商品描述")->rules("required");
        $form->radio("on_sale","上架")->options(["1"=>"是","0"=>"否"])->default("0");
        //众筹目标字段
        $form->text("crowdfunding.target_amount","众筹目标金额")->rules("required|numeric|min:0.01");
        $form->datetime("crowdfunding.end_at","众筹结束时间")->rules("required|date");
        //sku
        $form->hasMany("skus","商品SKU",function (Form\NestedForm $form){
            $form->text("title","SKU名称")->rules("required");
            $form->text("description","SKU描述")->rules("required");
            $form->text('price', '单价')->rules('required|numeric|min:0.01');
            $form->text('stock', '剩余库存')->rules('required|integer|min:0');
        });
        $form->saving(function (Form $form){
            $form->model()->price = collect($form->input("skus"))->where(Form::REMOVE_FLAG_NAME,0)->min("price");
        });
        return $form;
    }
}
