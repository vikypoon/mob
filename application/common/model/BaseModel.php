<?php
namespace app\common\model;
use think\Model;
use think\Request;
use think\Session;
class BaseModel extends Model
{
	 public static function init()
    {
        parent::init();
        // 获取当前域名
        // self::$base_url = base_url();
    }

     /**
     * 获取当前域名
     * @return string
     */
    // protected static function baseUrl()
    // {
    //     $request = Request::instance();
    //     $host = $request->scheme() . '://' . $request->host();
    //     $dirname = dirname($request->baseUrl());
    //     return empty($dirname) ? $host : $host . $dirname . '/';
    // }

    
}