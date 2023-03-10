@extends('layouts.app')
@section('title', '收货地址列表')

@section('content')
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card panel-default">
                <div class="card-header">
                    收货地址列表
                    <a href="{{ route('user_addresses.create') }}" class="float-right"  style="float: right;">新增收货地址</a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>收货人</th>
                            <th>地址</th>
                            <th>邮编</th>
                            <th>电话</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($addresses as $address)
                            <tr>
                                <td>{{ $address->contact_name }}</td>
                                <td>{{ $address->full_address }}</td>
                                <td>{{ $address->zip }}</td>
                                <td>{{ $address->contact_phone }}</td>
                                <td>
{{--                                    <button class="btn btn-primary" >修改</button>--}}
{{--                                    <button class="btn btn-danger">删除</button>--}}
                                    <a class="btn btn-primary" href="{{ route("user_addresses.edit",["user_address"=>$address->id]) }}">修改</a>
                                    <button class="btn btn-danger btn-del-address" data-id="{{ $address->id }}">删除</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scriptsAfterJs')
    <script>
        $(document).ready(function (){
            $(".btn-del-address").click(function (){
                var id = $(this).data('id')
                //调用sweetalert
                swal({
                    title:'确认删除？',
                    icon:'warning',
                    buttons:['取消','确定'],
                    dangerMode: true,
                }).then(function (willDelete){
                    if(!willDelete){
                        return
                    }
                    axios.delete("/user_addresses/"+id).then(function (){
                        location.reload()
                    })
                })
            })
        })
    </script>
@endsection
