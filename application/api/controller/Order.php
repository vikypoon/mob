<?php
namespace app\api\controller;
use app\api\controller\Controller;
use think\Request;
use think\Db;
use app\api\model\Shopcart as ShopcartModel;
use app\api\model\Order as OrderModel;
use app\api\model\Wxapp as WxappModel;
use app\common\library\wechat\WxPay;
/**
* 
*/
class Order extends Controller
{
	
	private $user;

	public function _initialize()
	{	
		parent::_initialize();
		$this->user = $this->getUser();
		$this->model = new OrderModel($this->user);
	}

	// 8.25订单立即购买
	public function orderNow($goods_id, $goods_num, $goods_price, $goods_sku_id, $coupon_use_id = null, $remark)
	{
		$model = new OrderModel();
		// if (!$this->model->getBuyNow($this->user, $goods_id, $goods_num, $goods_sku_id, $coupon_id=null, $remark='')) {
		// // print_r($order);die;
		// 	return json_encode(['code'=>0, 'msg'=>$this->model->getError()], JSON_UNESCAPED_UNICODE);
		// }
		$order = $model->getBuyNow($this->user, $goods_id, $goods_num,  $goods_price,$goods_sku_id, $coupon_id=null, $remark);
		if (!$this->request->isPost()) {
			return json_encode(['code'=> 0, 'data'=>$order], JSON_UNESCAPED_UNICODE);
		}
		// 创建订单
		// dump($goods_id);
		// dump($goods_num);
		// dump($goods_price);
		// dump( $goods_sku_id);
		// dump($coupon_use_id);
		// dump($remark);die;
		if ($model->createOrder($this->user, $order, $coupon_use_id, $remark)) {
			return json_encode([
					// 'payment' => $this->unifiedorder($model, $this->user),
					'order_id' => $model['order_id']
				],JSON_UNESCAPED_UNICODE);
		}else{
			
		print_r(33);
		}
	}

	// 提交订单--确认购买
	public function buyNow($goods_id,$goods_num,$goods_sku_id,$coupon_id = null,$remark = '')
	{
		$model = new OrderModel();
		// print_r($model);
		$order = $model->getBuyNow($this->user,$goods_id,$goods_num,$goods_sku_id);
		if (!$this->request->isPost()) {
			return $this->renderSuccess($order);
		}
		if ($model->hasEorror()) {
			return $this->renderError($model->getError);
		}
		//创建订单
		if ($model->createOrder($this->user['user_id'],$order,$coupon_id,$remark)) {
			return $this->renderSuccess([
					'payment' => $this->unifiedorder($model,$this->user),
					'order_id' => $model['order_id']
				]);
		}
		$error = $model->getError() ? : '订单创建失败';
		return $this->renderError($error);
	}

	// 确认订单--购物车结算
	public function cart($cart_ids,$coupon_id = null, $remark = '')
	{
		//商品结算信息
		$Card = new ShopcartModel();
		$order = $Card->getList($this->user,$cart_ids);
		if (!$this->request->isPost()) {
			return $this->renderSuccess($order);
		}
		//创建订单
		$model = new OrderModel;
		// print_r($remark);
		// print_r($order);die;
		if ($model->createOrder($this->user,$order,$coupon_id,$remark)) {
			//移除购物车中已下单的商品
			$Card->del($cart_ids);
			print_r(666);die;
			//发起微信支付
			return $this->renderSuccess([
					'payment' => $this->unifiedorder($model,$this->user),
					'order_id' => $model['order_id']
				]);
		}
		return $this->renderError($model->getError() ?: '订单创建失败');
	}

	// 构建微信支付
	private function unifiedorder($order,$user)
	{	
		// print(222);die;
		$wxConfig = WxappModel::getWxappCache();
		$Wxpay = new Wxpay($wxConfig);
		$payment = $Wxpay->unifiedorder($order['order_no'],$user['open_id'],$order['pay_price']);
		//记录prepay_id
		$model = new WxappPrepayIdModel;
		$model->add($payment['prepay_id'],$order['order_id'],$user['user_id']);
		return $payment;
	}



	
}