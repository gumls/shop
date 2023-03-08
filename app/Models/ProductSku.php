<?php

namespace App\Models;

use App\Exceptions\InternalException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSku extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'price', 'stock'];

    //关联产品
    public function product(){
        return $this->belongsTo(Product::class);
    }

    //减库存操作
    public function decreaseStock($amount){
        if($amount < 0){
            throw new InternalException("减库存不可小于0");
        }
        return $this->where("id",$this->id)->where("stock",">=",$amount)->decrement("stock",$amount);
    }
    //加库存操作
    public function addStock($amount){
        if($amount < 0){
            throw new InternalException("加库存不可小于0");
        }
        return $this->increment("stock",$amount);
    }
}
