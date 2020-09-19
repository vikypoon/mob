<?php
namespace app\common\model;

use think\Model;

class Cart extends BaseModel
{
	protected $name = 'cart';
	// 添加

	public function sku()
	{
		$this->hasMany('GoodsSku');
	}

	public function add($goods_id,$goods_num,$goods_sku_id)
	{	
		return 111;
		// $goods = Goods::detail($goods_id);
		// print_r($goods);die;
	}

	public function cartList($uid)
	{
		$model = new static;
		$list = $model->where('user_id', '=', $uid)->select()->toArray();
		$goodsStatus = [];
		foreach ($list as $key => $value) {
			$goods = model('goods')->field('goods_id,goods_name,goods_status')->where('goods_id',$value['goods_id'])->find()->toArray();
			$image_url = model('goodsImage')->where('goods_id',$value['goods_id'])->value('goods_url');
			$sku = model('goodsSku')->where('goods_sku_id',$value['goods_sku_id'])->find()->toArray();
			$spec = model('specValue')->where('spec_value_id',$sku['spec_sku_id'])->value('spec_value');
			// print_r($goodsStatus);die;
			if ($goods['goods_status'] != 10) {
				return json_encode(['msg'=>'商品已下架']);
			}
			$goodsStatus[$key]['goods'] = $goods;
			$goodsStatus[$key]['sku'] = $sku;
			$goodsStatus[$key]['cart_num'] = $value['goods_num'];
			$goodsStatus[$key]['image_url'] = $image_url;
			$goodsStatus[$key]['spec'] = $spec;
			$goodsList = $goodsStatus;
		}
		return $goodsList;
		
	}
}