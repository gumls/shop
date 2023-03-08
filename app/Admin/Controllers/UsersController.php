<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UsersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'User';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());

//        $grid->column('id', __('Id'));
//        $grid->column('name', __('Name'));
//        $grid->column('email', __('Email'));
//        $grid->column('email_verified_at', __('Email verified at'))->display(function ($v){
//            return $v ? '是' : '否';
//        });
//        $grid->column('password', __('Password'));
//        $grid->column('remember_token', __('Remember token'));
//        $grid->column('created_at', __('Created at'));
//        $grid->column('updated_at', __('Updated at'));
        $grid->id("ID");
        $grid->name("用户名");
        $grid->email("邮箱");
        $grid->email_verified_at("已验证邮箱")->display(function ($v){
            return $v ? '是' : '否';
        });
        $grid->created_at("注册时间");
        $grid->updated_at("更新时间");
        //不在页面显示新建按钮
        $grid->disableCreateButton();
        //不显示编辑按钮
        $grid->disableActions();

        $grid->tools(function ($tools){
            //禁用批量删除按钮
            $tools->batch(function ($batch){
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
//    protected function detail($id)
//    {
//        $show = new Show(User::findOrFail($id));
//
//        $show->field('id', __('Id'));
//        $show->field('name', __('Name'));
//        $show->field('email', __('Email'));
//        $show->field('email_verified_at', __('Email verified at'));
//        $show->field('password', __('Password'));
//        $show->field('remember_token', __('Remember token'));
//        $show->field('created_at', __('Created at'));
//        $show->field('updated_at', __('Updated at'));
//
//        return $show;
//    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
//    protected function form()
//    {
//        $form = new Form(new User());
//
//        $form->text('name', __('Name'));
//        $form->email('email', __('Email'));
//        $form->datetime('email_verified_at', __('Email verified at'))->default(date('Y-m-d H:i:s'));
//        $form->password('password', __('Password'));
//        $form->text('remember_token', __('Remember token'));
//
//        return $form;
//    }
}
