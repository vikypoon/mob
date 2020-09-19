<?php
namespace app\api\model;
use think\Model;
use think\Db;
use app\common\model\Shopcart as ShopcartModel;
use app\common\model\Goods;
use app\common\model\GoodsSku ;
class Shopcart extends ShopcartModel
{
	public $table = 'ye_Shopcart';

	// private $user_id;

	// public function __construct($user_id)
	// {	
	// 	$this->user_id = $user_id;
		
	// }

	// 加入购物车
	public function add($uid, $goods_id, $goods_num, $goods_sku_id, $goods_name, $store_id, $goods_price, $goods_img,$goods_desc)
	{
		// $uid = $this->user_id;
		$goodsModel = new Goods;
		$goods = $goodsModel->detail($goods_id);
		if (!$goods||$goods['goods_status'] != 10) {
			$this->setError('很抱歉，商品信息不存在或已下架');
            return false;
		}
		// 商品sku信息
		$goodsSku = $goodsModel->goodsSku($goods_sku_id);
		if ($goods_num > $goodsSku['stock_num']) {
			$this->setError('很抱歉，商品库存不足');
            return false;
		}
		$Shopcart = Db::name('shopcart');
		$map = array('user_id'=>$uid, 'goods_sku_id'=>$goods_sku_id, 'goods_id'=>$goods_id);
		$isset = $Shopcart->where($map)->find();
		// print_r($isset);die;
		if (!empty($isset)) {
			$goods_num += $isset['total_num'];
			$result = $this->setNumberById($uid,$goods_id,$goods_num,$goods_sku_id);
			if ($result) {
				return true;
			}else{
				return false;
			}
		}else{
			$cart =  $Shopcart->insert([
				'user_id'  => $uid,
				'goods_id' => $goods_id,
				'total_num'=> $goods_num,
				'goods_sku_id'=> $goods_sku_id,
				'goods_name' =>$goods_name,
				'store_id' =>$store_id,
				'goods_price'=>$goods_price,
				'goods_img'=>$goods_img,
				'goods_desc'=>$goods_desc,
				'addtime'  => time(),
				'updatetime' => time()
				]);

			if ($cart) {
				return true;
			}else{
				return false;
			}
		}
		


	}

	// 删除购物车
	public function del($cart_id)
	{	
		$indexArr = strpos($cart_id, ',') !==false ? explode(',', $cart_id) :[$cart_id];
		foreach ($indexArr as $v) {
			$cart = Db::name('shopcart')->where('cart_id','=', $v)->delete();
		}
		if ($cart) {
			return array('code'=>1, 'msg'=>'删除成功');
		}else{
			$this->setError('删除失败');
			return false;
		}
	}

	// 修改购物车数量
	 private function setNumberById($uid,$goods_id,$goods_num,$goods_sku_id){
        $map=array(
            'user_id'=>$uid,
            'goods_id'=>$goods_id,
            'goods_sku_id'=>$goods_sku_id
            );
        $Shopcart = Db::name('shopcart');
        $result=$Shopcart->where($map)->setField('total_num',$goods_num);
       	if ($result) {
	        $res=array(
	            'error_code'=>0,
	            'error_message'=>'操作成功'
	            );
        return $res;
       	}
    }

	// 购物车列表
	public function getList($uid, $cart_ids = null)
    {
    	if (is_null($cart_ids)) {
    		// return '购物车列表';
    		$carts = Db::name('shopcart')->where('user_id', '=', $uid)->select()->toArray();
    	}else{
    		// return '你到结算啦';
    		$cartsIds = strpos($cart_ids, ',') !==false ? explode(',', $cart_ids) : [$cart_ids];
    		foreach ($cartsIds as  $set) {
    			$carts[] = Db::name('shopcart')->where(['user_id'=>$uid, 'cart_id'=>$set])->find();
    		}		
    	}
    	// 判断商品
    	if (!empty($carts)) {
    			$sku = new GoodsSku();
    			$goods = new Goods();
    			foreach ($carts as  &$value) {
    				// sku库存
	    				$goods_sku = $sku->getSku($value['goods_sku_id']);
	    				// print_r($goods_sku);die;
	    				$goods_info = $goods->goodsInfo($value['goods_id']);
	    				// 判断商品是否下架
	    				if ($goods_info['goods_status'] !== 10) {
	    					$value['status_err_msg'] = '很抱歉，商品已下架';
	    				}
    					
	    				// 判断是否库存
	    				if ($value['total_num'] > $goods_sku['stock_num']) {
	    						$value['num_err_msg'] = '很抱歉，该商品库存不足';
	    				}

	    				// 判断商品是否降价
	    				if ($value['goods_price'] > $goods_sku['goods_price']) {
	    					$value['low_price'] = $value['goods_price'] - $goods_sku['goods_price'];
	    				}
	    				$value['deduct_stock_type'] = $goods_info['deduct_stock_type'];
	    				$value['spec_type'] = $goods_info['spec_type'];
	    				$value['content'] = $goods_info['content'];
	    				$value['image'] = $goods->image()->where('goods_id', '=', $value['goods_id'])->select()->toArray();
	    				$value['sku']['goods_no'] = $goods_sku['goods_no'];
						$value['sku']['goods_price'] = $goods_sku['goods_price'];
						$value['sku']['line_price'] = $goods_sku['line_price'];
						$value['sku']['goods_sku_id'] = $goods_sku['goods_sku_id'];
						$value['sku']['goods_weight'] = $goods_sku['goods_weight'];
	    				// print_r($value['image']);die;
    			$value['total_price'] = bcmul($value['goods_price'], $value['total_num'], 2);
    			}
    		}
    	// 商品总价
    	$orderTotalPrice = array_sum(array_column($carts, 'total_price'));
    	$user = (new User)->getUser($uid);
    	$couponList = (new UserCoupon)->getUserCouponList($uid, $orderTotalPrice);
    	// print_r(array_values($couponList));die;
    	return [
    		'goods_list' => $carts,
    		'order_total_price' => $orderTotalPrice,
    		'order_pay_price' => $orderTotalPrice,
    		'address' => $user['address'],
    		'coupon_list' => $couponList
    	];
    }

    public function clearAll($cart_ids)
    {	
    	$cart_ids = explode(',', $cart_ids);
    	// print_r($cart_ids);die;
    	if (!empty($cart_ids)) {
	    	foreach ($cart_ids as  $value) {
	    		$this->where('cart_id', '=', $value)->delete();
	    	}
    	}
    		return true;
    }

    private  function setError($error)
    {
    	empty($this->error) && $this->error = $error;
    }
}

