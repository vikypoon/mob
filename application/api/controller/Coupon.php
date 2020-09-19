<?php
namespace app\api\controller;
use think\Controller;
use think\Request;
use think\Db;
use app\api\model\Coupon as Couponmodel;
/**
* 
*/
class Coupon extends Controller
{
	
	public function add()
	{
		$model = new Couponmodel;
		$data = input("post.");
		$add = $model->add($data);
		if ($add) {
			return '成功了';
		}else{
			return '失败了';
		}
	}

	public function receive()
	{
		$model = new Couponmodel;
		$data = strip_tags(input("post."));
		print_r($data);die;
		$add = $model->add($data);
	}


}