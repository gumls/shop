<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
    const TYPE_NORMAL = "normal";
    const TYPE_CROWDFUNDING = "crowdfunding";
    public static $typeMap = [
        self::TYPE_NORMAL => "普通商品",
        self::TYPE_CROWDFUNDING => "众筹商品",
    ];
    protected $fillable = [
        'title', 'description', 'image', 'on_sale',
        'rating', 'sold_count', 'review_count', 'price','type',
    ];

    protected $casts = [
        'on_sale' => 'boolean' //on_sale 是一个布尔类型的字段
    ];

    //关联sku
    public function skus(){
        return $this->hasMany(ProductSku::class);
    }

    //关联栏目
    public function category(){
        return $this->belongsTo(Category::class);
    }

    //关联众筹商品
    public function crowdfunding(){
        return $this->hasOne(CrowdfundingProduct::class);
    }

    public function getImageUrlAttribute(){
        // 如果 image 字段本身就已经是完整的 url 就直接返回
        if(Str::startsWith($this->attributes["image"],["http://","https://"])){
            return $this->attributes["image"];
        }
        return Storage::disk("public")->url($this->attributes["image"]);
    }
}
