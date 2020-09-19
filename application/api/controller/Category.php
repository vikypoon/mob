<?php
namespace app\api\controller;
use think\Controller;
use think\Request;
use think\Db;
use app\common\model\Category as CategoryModel;
/**
* 
*/
class Category extends Controller
{	
	public function index()
	{	
		$cate_id = 1;
		// $model = model('Category');
		$goods = CategoryModel::cateList($cate_id)->toArray();
		// $goods = json_decode(json_encode($goods));
		print_r($goods);
	}


	public function allCate()
	{
		$model = model('Category');
		$cate = $model->allCategory();
		print_r($cate);
	}
}