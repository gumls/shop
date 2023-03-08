<?php
use Illuminate\Support\Facades\Route;
function test_helper(){
    return 'OK';
}
function route_class(){
    return str_replace(".","-",Route::currentRouteName());
}

function str(){
    return str_pad("1",6,"t",STR_PAD_LEFT);
}

function ngrok_url($rout_name,$parameters = []){
    if(app()->environment("local") && $url = config("app.ngrok_url")){
        return $url.\route($rout_name,$parameters,false);
    }
    return \route($rout_name,$parameters);
}
