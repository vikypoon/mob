<?php
namespace app\common\model;
use think\Db;
class GoodsSku extends BaseModel
{
	protected $name = 'goods_sku';

	public function getSku($goods_sku_id)
	{
		return (new static)->where('goods_sku_id', '=', $goods_sku_id)->find();
	}
}