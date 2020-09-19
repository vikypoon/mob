<?php
namespace app\api\controller;
use think\Controller;
use think\Request;
use think\Db;
// use app\api\model\Goods as GoodsModel;
use app\common\model\Goods as GoodsModel;
/**
* 
*/
class Goods extends Controller
{	
	public function detail()
	{	
		$goods_id = 1;
		$goods = GoodsModel::detail($goods_id);
		// print_r($model);
		return $goods;
		
	}

	public function getList()
	{
		$model = new  GoodsModel;
		$list = $model->getList()->toArray();
		$goodsList = json_encode(['code' => 200, 'msg' => 'success', 'data' => $list],JSON_UNESCAPED_UNICODE);
		 return $goodsList;
		
	}

	public function burl(){
		$url = $this->base_url();
		print_r($url);
	}

	//获取当前url
	function base_url()
	{
	    $request = Request::instance();
	    $subDir = str_replace('\\', '/', dirname($request->server('PHP_SELF')));
	    return $request->scheme() . '://' . $request->host() . $subDir . ($subDir === '/' ? '' : '/');
	}
	
}