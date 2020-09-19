<?php
namespace app\api\controller;
use app\api\controller\Controller;
use think\Request;
use think\Db;
use app\api\model\Shopcart as ShopcartModel;
// use app\common\model\Goods as GoodsModel;
/**
* 
*/
class Shopcart extends Controller
{	
	private $user;

	public function _initialize()
	{	
		parent::_initialize();
		$this->user = $this->getUser();
		$this->model = new ShopcartModel($this->user);
	}


	// 购物车列表
	public function lists()
	{	
		$uid = $this->user;
		// 购物车列表
		$list = $this->model->getlist($uid);
		// 结算
		// $cart = new ShopcartModel($this->user);
		// $rs = $cart->getList($uid, $cart_ids);
		return json_encode(['code'=>1, 'msg'=> 'success', 'data' =>$list],JSON_UNESCAPED_UNICODE);
	}

	// 加入购物车
	public function add($goods_id, $goods_num, $goods_sku_id, $goods_name, $store_id, $goods_price, $goods_img,$goods_desc)
	{
		$uid = $this->user;
		if (!$this->model->add($uid, $goods_id, $goods_num, $goods_sku_id, $goods_name, $store_id, $goods_price, $goods_img,$goods_desc)) {
			return json_encode(['code'=>0, 'msg'=>$this->model->getError()], JSON_UNESCAPED_UNICODE);
			// return $this->renderError($this->model->getError() ?: '加入购物车失败');
		}
		return json_encode('加入购物车成功',JSON_UNESCAPED_UNICODE );

	}

	public function delCart($cart_id)
	{
		if (!$this->model->del($cart_id)) {
			return json_encode(['code'=>0, 'msg'=>$this->model->getError()], JSON_UNESCAPED_UNICODE);
		}
		return json_encode('删除成功',JSON_UNESCAPED_UNICODE );
	}

	// 结算
	public function settlement()
	{	
		$uid = $this->user;
		$model = new ShopcartModel;
		$cart_ids = '1';
		$list = $model->getlist($uid, $cart_ids);
		return json_encode(['code'=>1, 'msg'=> 'success', 'data' =>$list],JSON_UNESCAPED_UNICODE);
	}

}