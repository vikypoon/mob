<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
  function usr_fnc($url,$servername){
    //$servername .= "/public";
    if($url && stripos($url,$servername) !== 0){
      //去除url前面的
      $url = ltrim($url,".");
      $url = ltrim($url,"/");
      $url = $servername . "/" . $url;
    }
    return $url;
  }

  function fileUrl($url){
    $http = "http";
    if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on"){
      $http = "https";
    }
    $servername = $http."://".$_SERVER["SERVER_NAME"];

    if(is_array($url)){
      array_walk_recursive($url,function(&$v,$key,$servername){
        $v = usr_fnc($v,$servername);
      },$servername);
    }else{
      $url = usr_fnc($url,$servername);
    }
    return $url;
  }

  /**
   * 二维数组排序
   * @param $arr
   * @param $keys
   * @param bool $desc
   * @return mixed
   */
  function array_sort($arr, $keys, $desc = false)
  {
      $key_value = $new_array = array();
      foreach ($arr as $k => $v) {
          $key_value[$k] = $v[$keys];
      }
      if ($desc) {
          arsort($key_value);
      } else {
          asort($key_value);
      }
      reset($key_value);
      foreach ($key_value as $k => $v) {
          $new_array[$k] = $arr[$k];
      }
      return $new_array;
  }