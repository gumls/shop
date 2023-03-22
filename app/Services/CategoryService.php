<?php
namespace App\Services;

use App\Models\Category;

class CategoryService {

    //递归方法
    //$parentId 代表取子类目的父类id null 代表获取所有根目录
    //$allCategories 参数代表数据库中所有的类目，如果是 null 代表需要从数据库中查询
    public function getCategoryTree($parentId = null,$allCategories = null){
        if(is_null($allCategories)){
            $allCategories = Category::all();
        }
        return $allCategories
            //从所有类目中挑选出父类id为$parentId的类目
            ->where("parent_id",$parentId)
            ->map(function (Category $category)use ($allCategories){
                $data = ["id"=>$category->id,"name"=>$category->name];
                if(!$category->is_directory){
                    return $data;
                }
                //否则递归调用
                $data["children"] = $this->getCategoryTree($category->id,$allCategories);
                return $data;
            });
    }
}
