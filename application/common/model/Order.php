<?php
namespace app\common\model;
use think\Hook;
class Order extends BaseModel
{
	
	protected $name = 'order';

	// 订单模型初始化
	public static function init()
	{
		parent::init();
		$static = new static;
		Hook::listen('order', $static);
	}

	// 订单商品列表
	public function goods()
	{
		return $this->hasMany('OrderGoods');
	}

	// 关联订单收货地址表
	public function address()
	{
		return $this->hasOne('OrderAddress');
	}

	// 生成订单号
	protected function orderNo()
	{
		return date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
	}

	public function detail($where)
	{
		is_array($where) ? $filter = $where : $filter['order_id'] = (int)$where;
		return self::get($filter, ['goods.image', 'address', 'express']);
	}
}