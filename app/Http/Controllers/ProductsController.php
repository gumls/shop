<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller {
    //
    public function index(Request $request){
        //构造一个查询构造器
        $bulider = Product::query()->where("on_sale",true);
        // 判断是否有提交 search 参数，如果有就赋值给 $search 变量
        // search 参数用来模糊搜索商品
        if($search = $request->input("search","")){
            $like = "%".$search."%";
            $bulider->where(function ($query)use ($like){
                $query->where("title","like",$like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas("skus",function ($query)use ($like){
                        $query->where("title","like",$like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }
        //是否有排序
        if($order = $request->input("order","")){
            if(preg_match("/^(.+)_(asc|desc)$/",$order,$m)){
                if(in_array($m[1],['price', 'sold_count', 'rating'])){
                    $bulider->orderBy($m[1],$m[2]);
                }
            }
        }

        $products = $bulider->paginate(16);
        return view("products.index",[
            "products" => $products,
            "filters"  => [
                "search" => $search,
                "order"  => $order,
            ]
        ]);
    }
    public function show(Product $product,Request $request){
        if(!$product->on_sale){
//            throw new \Exception("商品未上架");
            throw new InvalidRequestException("商品未上架");
        }
        //是否收藏
        $favored = false;
        if($user = $request->user()){
            $favored = boolval($user->favoriteProducts()->find($product->id));
        }
        //评价
        $reviews = OrderItem::query()
            ->with(['order.user', 'productSku']) // 预先加载关联关系
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at') // 筛选出已评价的
            ->orderBy('reviewed_at', 'desc') // 按评价时间倒序
            ->limit(10) // 取出 10 条
            ->get();
        return view("products.show",["product"=>$product,"favored"=>$favored,"reviews"=>$reviews]);
    }

    //收藏
    public function favor(Request $request,Product $product){
        $user = $request->user();
        if($user->favoriteProducts()->find($product->id)){
            return [];
        }
        $user->favoriteProducts()->attach($product);
        return [];
    }
    //取消收藏
    public function disFavor(Request $request,Product $product){
        $user = $request->user();
        $user->favoriteProducts()->detach($product);
        return [];
    }
    //收藏列表
    public function favorites(Request $request){
        $user = $request->user();
        $products = $user->favoriteProducts()->paginate(16);
        return view("products.favorites",["products"=>$products]);
    }
}
