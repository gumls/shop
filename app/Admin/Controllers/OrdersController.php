<?php

namespace App\Admin\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Admin\HandleRefundRequest;
use App\Http\Requests\Request;
use App\Models\Order;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Foundation\Validation\ValidatesRequests;

class OrdersController extends AdminController
{
    use ValidatesRequests;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '订单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order());
        //只展示已支付的订单
        $grid->model()->whereNotNull("paid_at")->orderBy("paid_at","desc");
        $grid->no("流水号");
        //展示关联关系的字段
        $grid->column("user.name","买家");
        $grid->total_amount("总金额")->sortable();
        $grid->paid_at("支付时间")->sortable();
        $grid->ship_status("物流")->display(function ($value){
            return Order::$shipStatusMap[$value];
        });
        $grid->refund_status("退款")->display(function ($value){
            return Order::$refundStatusMap[$value];
        });
        //禁用创建按钮
        $grid->disableCreateButton();
        $grid->actions(function ($actions){
            //禁用删除和编辑按钮
            $actions->disableDelete();
            $actions->disableEdit();
        });
        //禁用批量删除按钮
        $grid->tools(function ($tools){
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
    protected function detail($id)
    {
        $show = new Show(Order::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('no', __('No'));
        $show->field('user_id', __('User id'));
        $show->field('address', __('Address'));
        $show->field('total_amount', __('Total amount'));
        $show->field('remark', __('Remark'));
        $show->field('paid_at', __('Paid at'));
        $show->field('payment_method', __('Payment method'));
        $show->field('payment_no', __('Payment no'));
        $show->field('refund_status', __('Refund status'));
        $show->field('refund_no', __('Refund no'));
        $show->field('closed', __('Closed'));
        $show->field('reviewed', __('Reviewed'));
        $show->field('ship_status', __('Ship status'));
        $show->field('ship_data', __('Ship data'));
        $show->field('extra', __('Extra'));
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
        $form = new Form(new Order());

        $form->text('no', __('No'));
        $form->number('user_id', __('User id'));
        $form->textarea('address', __('Address'));
        $form->decimal('total_amount', __('Total amount'));
        $form->textarea('remark', __('Remark'));
        $form->datetime('paid_at', __('Paid at'))->default(date('Y-m-d H:i:s'));
        $form->text('payment_method', __('Payment method'));
        $form->text('payment_no', __('Payment no'));
        $form->text('refund_status', __('Refund status'))->default('pending');
        $form->text('refund_no', __('Refund no'));
        $form->switch('closed', __('Closed'));
        $form->switch('reviewed', __('Reviewed'));
        $form->text('ship_status', __('Ship status'))->default('pending');
        $form->textarea('ship_data', __('Ship data'));
        $form->textarea('extra', __('Extra'));

        return $form;
    }

    //订单详情
    public function show($id,Content $content){
        return $content->header("查看订单")
            ->body(view("admin.orders.show",["order"=>Order::find($id)]));
    }
    //发货控制器
    public function ship(Order $order,Request $request){
        if(!$order->paid_at){
            throw new InvalidRequestException("订单未付款");
        }
        //判断是否为未发货
        if($order->ship_status !== Order::SHIP_STATUS_PENDING){
            throw new InvalidRequestException("订单已发货");
        }
        $data = $this->validate($request,[
            'express_company' => ['required'],
            'express_no'      => ['required'],
        ],[],[
            'express_company' => '物流公司',
            'express_no'      => '物流单号',
        ]);
        $order->update([
            "ship_status" => Order::SHIP_STATUS_DELIVERED,
            "ship_data"   => $data,
        ]);

        return redirect()->back();
    }

    //处理退款
    public function handleRefund(Order $order,HandleRefundRequest $request){
        if($order->refund_status !== Order::REFUND_STATUS_APPLIED){
            throw new InvalidRequestException("订单状态不正确");
        }

        //是否同意退款
        if($request->input("agree")){

        } else {
            $extra = $order->extra?:[];
            $extra["refund_disagree_reason"] = $request->input("reason");

            $order->update([
                "refund_status" => Order::REFUND_STATUS_PENDING, //状态调整为未退款
                "extra"         => $extra,
            ]);
        }

        return $order;
    }
}