<?php
namespace app\api\controller;
use app\api\controller\Controller;
use think\Request;
use think\Db;
use think\Model;
use app\api\model\Cart as CartModel;
use think\Cookie;
/**
* 
*/
Class Cart extends Controller{

	private  $user;

	private  $model;

	public function _initialize(){
		parent::_initialize();
		$this->user = $this->getUser();
		// $this->model = new CartModel($this->user['user_id']);
	}

	public function index(){

		return $this->renderSuccess('成功');
	}

	public function lists()
	{
		$uid = $this->user;
		print_r($uid);die;
		// $model = model('category');
		$model = new CartModel;
		$rs = $model->getList($uid);
		return json_encode(['code' => 200,'data'=>$rs]);

	}

	// 加入购物车
	public function add()
	{	
		$cart = model('cart');
		$goods_id = 1;
		$goods_num = 1;
		$goods_sku_id = 1;
		$cart = new CartModel;
		// print_r($cart);
		if (!$cart->add($goods_id,$goods_num,$goods_sku_id)) {
			return $this->renderError('加入购物车失败');
			echo 222;
		}
			echo 1212;die;
	}

	public function addCart(Request $request ,$productId)
	{
		$bk_cart = Cookie::set('bk_cart');
		
		$bk_cart_arr = ($bk_cart != null ? explode(',', $bk_cart) : array());
		$count = 1;
		print_r($bk_cart);die;
		foreach ($bk_cart_arr as &$value) {
			$index = strpos($value, ':');
			if (substr($value, 0,$index) == $productId) {
				$count = ((int)substr($value, $index+1)) + 1;
				$value = $productId .':'.$count;
				break;
			}
		}

		if ($count ==1) {
			array_push($bk_cart_arr, $productId.':'.$count);
		}
		return $bk_cart_arr;
		// $m3_result = new M3Result;
		// $m3_result->status = 0;
		// $m3_result->message = '添加成功';
		// return reponse($m3_result->toJson())->withCookie('bk_cart',implode(',', $bk_cart_arr));
	}
}
