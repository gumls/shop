<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use \App\Models\Product;

class ProductsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商品';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product());
        //预加载商品类目
        $grid->model()->with(["category"]);
        $grid->id('ID')->sortable();
        $grid->title('商品名称');
        $grid->column("category.name","类目");
        $grid->on_sale('已上架')->display(function ($value) {
            return $value ? '是' : '否';
        });
        $grid->price('价格');
        $grid->rating('评分');
        $grid->sold_count('销量');
        $grid->review_count('评论数');

        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });
        $grid->tools(function ($tools) {
            // 禁用批量删除按钮
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

        //创建商品 第一个是tiitle 商品名称
        $form->text("title","商品名称")->rules("required");
        $form->select("category_id","类目")->options(function ($id){
            $category = Category::query()->find($id);
            if($category){
                return [$category->id => $category->name];
            }
        })->ajax("/admin/api/categories?is_directory=0");
        //创建一个选择图片狂
        $form->image("image","图片")->rules("required|image");
        //富文本编辑器
        $form->quill('description', '商品描述')->rules('required');
        //单选框
        $form->radio("on_sale","上架")->options(["1"=>"是","0"=>"否"])->default("0");
        //直接添加一对多关联模型
        $form->hasMany("skus","sku列表",function (Form\NestedForm $form){
            $form->text("title","SKU名称")->rules("required");
            $form->text("description","SKU描述")->rules("required");
            $form->text("price","单价")->rules("required|numeric|min:0.01");
            $form->text("stock","剩余库存")->rules("required|integer|min:0");
        });

        //定义事件回调
        $form->saving(function (Form $form){
            $form->model()->price = collect($form->input("skus"))
                ->where(Form::REMOVE_FLAG_NAME,0)
                ->min("price")?:0;
        });
        return $form;
    }
}
