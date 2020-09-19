<?php
namespace app\api\model;
use app\common\model\Goods;
use app\common\model\Order as OrderModel;
use think\Db;
use app\common\model\User as UserModel;
use app\api\model\UserCoupon;
use app\common\model\DeliveryRule;
class Order extends OrderModel
{	
	// 订单确认--立即购买
	public function getBuyNow($user, $goods_id, $goods_num,  $goods_price, $goods_sku_id)
	{
		//商品信息
		$goods = Goods::detail($goods_id);
		// print_r($goods['delivery_id']);die;
		//判断商品是否下架
		if (!$goods ||  $goods['goods_status'] !== 10) {
			$this->setError(['msg' => "很抱歉，商品信息不存在或者已下架"]);
			
		}
		//商品sku信息
		// $goods['goods_sku'] = $goods->getGoodsSku($goods_sku_id);
		$goodsModel = new Goods;
		$goodsSku = $goodsModel->goodsSku($goods_sku_id);
		$goods['sku'] = $goodsSku;
		//判断商品库存
		if ($goods_num > $goodsSku['stock_num']) {
			$this->setError('很抱歉，商品库存不足');
		}
		//商品单价
		// $goods['goods_price'] = $goods['goods_sku']['goods_price'];
		//商品总价
		$goods['total_num'] = $goods_num;
		$totalPrice = bcmul($goods_price, $goods_num,2);
		$goods['total_price'] = $totalPrice;
		// print_r($total_price);die;
		//商品重量
		//$goods_total_weight = bcmul($goods['goods_sku']['goods_weight'], $goods_num,2);
		//当前用户收货城市id
		$userModel = new UserModel;
		$userinfo = $userModel->getUser($user);
		// print_r( $userinfo);die;
		$cityId = $userinfo['address'] ? $userinfo['address']['city_id'] : null;
		
		//是否存在收货地址
		// $exist_address = !$userinfo['address']->isEmpty();
		//验证用户收货地址是否存在运费规则中
		// if (!$intraRegion = $goods['delivery']->checkAddress($cityId)) {
		// 	$exist_address && $this->setError('很抱歉，您的收货地址不在配送范围内');
		// }
		$DeliveryRuleModel = new DeliveryRule;
		if (!$intraRegion = $DeliveryRuleModel->checkAdress($goods['delivery_id'], $cityId)) {
			$this->setError('很抱歉，您的收货地址不在配送范围内');
		}
		//计算配送费用
		// echo "string";die;
		// $expressPrice = $intraRegion ? $goods['delivery']->calcTotalFee($goods_num,$goods_total_weight,$cityId) : 0;
		$expressPrice = 0.00;
		//订单总金额
		// $orderPayPrice = bcadd($totalPrice, $expressPrice,2);
		//可用优惠券列表
		// print_r([$goods]);die;
		$couponList = (new UserCoupon)->getUserCouponList($user,$totalPrice);
		return [
			'goods_list' =>[$goods], //商品详情
			'order_total_num' => $goods_num, //商品总数量
			'order_total_price' => $totalPrice, //商品总金额（不含运费）
			'order_pay_price' => $totalPrice, //订单总金额
			'coupon_list' => array_values($couponList), //优惠券列表
			'address' => $userinfo['address'], //默认地址
			// 'exist_address' => $exist_address, //默认地址
			'express_price' => $expressPrice, // 配送费用
			'intra_region' => $intraRegion, //当前用户收货城市是否存在匹配规则
			// 'has_error' => $this-> hasError(),
			'error_msg' => $this->getError(),
		];

	}	

	// 创建新的订单9.5
	public function createOrder($user_id, $order, $coupon_use_id, $remark)
	{	
		// dump($user_id);
		// dump($coupon_use_id);
		// dump($order);die;
		if (empty($order['address'])) {
			$this->error = '请先选择收货地址';
			return false;
		}
		$this->setCouponprice($order, $coupon_use_id);
		Db::startTrans();
		try{
			// 记录订单信息
			$this->add($user_id, $order, $remark);
			// 保存订单商品信息
		    // print_r($goods_sku_id);die;
			$this->saveOrderGoods($user_id, $order);
			// 更新商品库存（针对下单减库存的商品）
			$this->updateGoodsStockNum($order['goods_list']);
			// 记录收货地址
			$this->saveOrderAddress($user_id, $order['address']);
			// dump(11111111111);die;
			Db::commit();
			return true;

		} catch (\Exception $e){
			dump( $e->getMessage());die;
			Db::rollback();
			$this->error = $e->getMessage();
			return false;
		}
		// return true;
	}

	
	// 创建新的订单
	// public function createOrder($user_id,$order,$coupon_id = null,$remark = '')
	// {
	// 	if (empty($roder['address'])) {
	// 		$this->error = '请选择收货地址';
	// 		return false;
	// 	}
	// 	//设置订单优惠券信息
	// 	$this->setCouponprice($order,$coupon_id);
	// 	Db::starTrans();
	// 	try {
	// 		//记录订单信息
	// 		$this->add($user_id,$order,$remark);
	// 		//保存订单商品信息
	// 		$this->saveOrderGoods($user_id,$order);
	// 		//更新商品库存（针对下单减库存的商品）
	// 		$this->updateGoodsStockNum($order['goods_list']);
	// 		//记录收货地址
	// 		$this->saveOrderAddress($user_id,$order['address']);
	// 		//记录分销商订单
	// 		DearOrderModel::createOrder($this,$this['goods']);
	// 		//事物提交
	// 		Db::commit();
	// 		return true;

	// 	} catch (\Exception $e) {
	// 		Db::rollback();
	// 		$this->error = $e->getMessage();
	// 		return false;
	// 	}
	// }

	// 设置订单优惠券信息
	public function setCouponprice(&$order, $coupon_use_id)
	{	
		// print_r($order['coupon_list']);die;
		if (!empty($order['coupon_list'])) {
			$couponInfo = [];
		// dump($coupon_use_id);
			foreach ($order['coupon_list'] as $coupon) {
				$coupon['user_coupon_id'] = $coupon_use_id && $couponInfo = $coupon;
				if (empty($couponInfo)) {
					return json_encode(['code'=>0, 'msg'=>'未找到优惠券信息']);
				}
				// 计算订单金额
				$orderTotalPrice = bcsub($order['order_total_price'], $couponInfo['reduce_price']);
				$orderTotalPrice <= 0 && $orderTotalPrice = '0.01';
				// 记录订单信息
				$order['coupon_id'] = $coupon_use_id;
				$order['coupon_price'] = $couponInfo['reduce_price'];
				// $order['order_pay_price'] = bcadd($orderTotalPrice, $order['express_price'], 2);
				// 设置优惠券使用状态
				// print_r($order);die;
				$model = UserCoupon::detail($coupon_use_id);
				// print_r($model);die;
				$model ->setIsUse();
				return true;
			}
			$order['coupon_id'] = 0;
			$order['coupon_price'] = 0.00;
			return true;
		}
	}
	// 设置订单优惠券信息
	
	// private function setCouponPrice(&order,$coupon_id)
	// {
	// 	if ($coupon_id > 0 && !empty($order['coupon_list'])) {
	// 		//获取优惠券信息
	// 		$couponInfo = [];
	// 		foreach ($order['coupon_list'] as $conpon) {
	// 			$coupon['user_conpon_id'] == $coupon_id && $couponInfo = $coupon;
	// 			if (empty($couponInfo)) throw new BaseException(['msg' => '未找到优惠券信息']);
	// 			//计算订单金额(抵扣后)
	// 			$orderTotalPrice = bcsub($order['order_total_price'], $couponInfo['reduce_price'],2);
	// 			$orderTotalPrice <= && $orderTotalPrice ='0.01';
	// 			//记录订单信息
	// 			$order['coupon_id'] = $coupon_id;
	// 			$order['coupon_price'] = $couponInfo['reduce_price'];
	// 			$order['order_pay_price'] = bcadd($orderTotalPrice, $order['express'],2);
	// 			//设置优惠券使用状态
	// 			$model = UserCoupon::detail($coupon_id);
	// 			$model->setIsuse()
	// 			return true;
	// 		}
	// 		$order['coupon_id'] = 0;
	// 		$order['coupon_price'] = 0.00;
	// 		return true;
	// 	}
	// }

	// 新增订单记录9.8
	private function add($user_id, &$order, $remarks = '')
	{	
		// print_r($order);die;
		return  $this->save([
				'user_id' => $user_id,
				// 'wxapp_id' => self::$wxapp_id,
				'order_no' => $this->orderNo(),
				'total_price' => $order['order_total_price'],
				'coupon_id' => $order['coupon_id'],
				'coupon_price' => $order['coupon_price'],
				'pay_price' => $order['order_pay_price'],
				// 'exPress_price' => $order['express_price'],
				'buyer_remark' => trim($remarks),
				'create_time' => time(),
			]);
	}
	// 新增订单记录
	// private function add($user_id, &$order, $remark ='')
	// {
	// 	return $this->save([
	// 			'user_id' => $user_id,
	// 			'wxapp_id' => self::$wxapp_id,
	// 			'order_no' => $this->orderNo(),
	// 			'total_price' => $order['order_total_price'],
	// 			'coupon_id' => $order['coupon_id'],
	// 			'coupon_price' => $order['coupon_price'],
	// 			'pay_price' => $order['order_pay_price'],
	// 			'express_price' => $order['express_price'],
	// 			'buy_remark' => trim($remark),
	// 		]);
	// }

	// 保存订单信息
	private function saveOrderGoods($user_id, $order)
	{
		// 订单商品列表
		// $goods = [];
		// print_r($order);die;
		foreach ($order['goods_list'] as  $value) {
			$goods['user_id'] = $user_id;
			$goods['goods_id'] = $value['goods_id'];
			$goods['goods_name'] = $value['goods_name'];
			$goods['image_id'] = $value['image'][0]['image_id'];
			$goods['deduct_stock_type'] = $value['deduct_stock_type'];
			$goods['spec_type'] = $value['spec_type'];
			$goods['total_num'] = $value['total_num'];
			$goods['total_price'] = $value['total_price'];
			$goods['content'] = $value['content'];
			// if (!empty($value['sku'])) {
			// 		print_r($value);
			// 		dump($goods_sku_id);die;
				// foreach ($value['sku'] as  $val) {
					// if ($goods_sku_id == $val['goods_sku_id']) {
			$goods['goods_no'] = $value['sku']['goods_no'];
			$goods['goods_price'] = $value['sku']['goods_price'];
			$goods['line_price'] = $value['sku']['line_price'];
			$goods['goods_sku_id'] = $value['sku']['goods_sku_id'];
			$goods['goods_weight'] = $value['sku']['goods_weight'];
					// }
				// }
			// }

		}
		$goodsList[] = $goods;
		return $this->goods()->saveAll($goodsList); 
	}

	// 保存订单信息
	// private function saveOrderGoods($user_id, &$order)
	// {
	// 	// 订单商品列表
	// 	$goodsList = [];
	// 	// 订单商品实付金额（不包含运费）
	// 	$realTotalPrice = bcsub($order['order_pay_price'], $order['express_price'], 2);
	// 	foreach ($order['goods_list'] as $goods) {
	// 		//计算商品实际付款额
	// 		$total_pay_price = $realTotalPrice * $goods['total_price'] / $order['order_total_price'];
	// 		$goodsList[] = [
	// 			'user_id' => $user_id,
	// 			'wxapp_id' => self::$wxapp_id,
	// 			'goods_id' => $goods['goods_id'],
	// 			'goods_name' => $goods['goods_name'],
	// 			'image_id' => $goods['image'][0]['image_id'],
	// 			'deduct_stock_type' => $goods['deduct_stock_type'],
	// 			'spec_type' => $goods['spec_type'],
	// 			'spec_sku_id' => $goods['goods_sku']['spec_sku_id'],
	// 			'goods_attr' => $goods['goods_sku']['goods_attr'],
	// 			'content' => $goods['content'],
	// 			'goods_no' => $goods['goods_sku']['goods_no'],
	// 			'goods_price' => $goods['goods_sku']['goods_price'],
	// 			'line_price' => $goods['goods_sku']['line_price'],
 //                'goods_weight' => $goods['goods_sku']['goods_weight'],
 //                'total_num' => $goods['total_num'],
 //                'total_price' => $goods['total_price'],
 //                'total_pay_price' => sprintf('%.2f', $total_pay_price),
	// 		];
	// 	}
	// 	return $this->goods()->saveAll($goodsList);
	// }

	// 更新商品库存9.14
	private function updateGoodsStockNum($goods_list)
	{
		$deductStockData = [];
		// print_r($goods_list);
		foreach ($goods_list as $goods) {
			 $deductStockData[] = [
				'goods_sku_id' => $goods['sku']['goods_sku_id'],
				'stock_num' => ['dec', $goods['total_num']]
			];
		}	
		if (!empty($deductStockData) && $goods['deduct_stock_type'] === 10) {
		 	(new GoodsSku)->isUpdate()->saveAll($deductStockData);
		 	return true;
		}

	}

	// 记录收货地址
	private function saveOrderAddress($user_id, $address)
	{
		return   $this->address()->save([
				'user_id' => $user_id,
				// 'wxapp_id' => self::$wxapp_id,
				'name' => $address['name'],
				'phone' => $address['phone'],
				'province_id' => $address['province_id'],
				'city_id' => $address['city_id'],
				'region_id' => $address['region_id'],
				'detail' => $address['detail'],
			]);

	}

	// 更新商品库存
	// private function updateGoodsStockNum($goods_list)
	// {
	// 	$deductStockData = [];
	// 	print_r(1111);die;
	// 	foreach ($goods_list as $goods) {
	// 		$goods['deduct_stock_type'] === 10 && $deductStockData[] = [
	// 			'goods_sku_id' => $goods['goods_sku']['goods_sku_id'],
	// 			'stock_num' => ['dec', $goods['total_num']]
	// 		];
	// 	}
	// 	!empty($deductStockData) && (new GoodsSku)->isUpdate()->saveAll($deductStockData);
	// }
	// 记录收货地址
	// private function saveOrderAddress($user_id, $adddress)
	// {
	// 	return $this->address()->save({
	// 		'user_id' => $user_id,
	// 		'wxapp_id' => self::$wxapp_id,
	// 		'name' => $address['name'],
	// 		'phone' => $address['phone'],
	// 		'province_id' => $address['province_id'],
	// 		'city_id' => $address['city_id'],
	// 		'region_id' => $address['region_id'],
	// 		'detail' => $address['detail'],
	// 	})
	// }

	// 用户中心订单列表
	public function getlist($user, $type = 'all')
	{
		//筛选条件
		$filter = [];
		//订单数据类型
		switch ($type) {
			case 'all':
				break;
			
			case 'payment':
				$filter['pay_status'] = 10;
				break;

			case 'delivery':
				$filter['pay_status'] = 20;
				$filter['delivery_status'] = 10;
				break;

			case 'received':
				$filter['pay_status'] = 20;
				$filter['delivery_status'] = 20;
				$filter['receipt_status'] = 10;
				break;

			case 'comment':
				$filter['order_status'] = 30;
				$filter['is_comment'] = 0;
				break;
		}
		return $this->with(['goods.image'])
			->where('user_id','=', $user_id)
			->where('order_status', '<>', 20)
			->where($filter)
			->order(['create_time' => 'desc'])
			->select();
	}

	// 取消订单
	public function cancel()
	{
		if ($this['pay_status']['value'] === 20) {
			$this->error = '已支付订单不可取消';
			return false;
		}
		//回退商品库存
		$this->backGoodsStock($this['goods']);
		return $this->save(['order_status' => 20]);
	}

	// 回退商品库存
	// private function backGoodsStock(&$goodsList)
	// {
	// 	$goodsSpaceSave = [];
	// 	foreach ($goodsList as $goods) {
	// 		//下单减库存
	// 		if ($goods['deduct_stock_type'] === 10) {
	// 			$goodsSpaceSave[] = [
	// 				'goods_sku_id' = $goods['goods_sku_id'];
	// 				'stock_num' = ['inc', $goods['total_num']]
	// 			];
	// 		}
	// 	}
	// 	return !empty($goodsSpaceSave) && (new GoodsSku)->isUpdate()->saveAll($goodsSpaceSave);
	// }

	//确认收货
	// public function receipt()
	// {
	// 	// 验证订单是否合法
	// 	if ($this->['delivery_status']['value'] === 10 || $this['receipt_status']['value'] === 20) {
	// 		$this->error = '该订单不合法';
	// 		return false;
	// 	}
	// 	$this->startTrans();
	// 	try{
	// 		//更新订单状态
	// 		$this->save([
	// 				'receipt_status' => 20,
	// 				'receipt_time' => time(),
	// 				'order_status' => 30
	// 			]);
	// 		//发送分销商佣金
	// 		DearOrderModel::grantMoney($this['order_id']);
	// 		$this->commit();
	// 		return true;
	// 	}catch (\Exception $e) {
	// 		$this->error = $e->getMessage();
	// 		$this->rollback();
	// 		return false;
	// 	}
	// }

	// 获取错误信息
	public function getError()
	{
		return $this->error;
	}

	private  function setError($error)
    {
    	empty($this->error) && $this->error = $error;
    }
}