<?php
namespace app\common\model;
class OrderGoods extends BaseModel
{
	
	protected $name = 'order_goods';
	protected $updateTime = false;
	
	// 订单商品信息
	public function image()
	{
		return $this->belongsTo('UploadFile', 'image_id', 'file_id');
	}

	// 关联商品表
	public function goods()
	{
		return $this->belongsTo('Goods');
	}

	// 关联商品SKU表
	public function sku()
	{
		return $this->belongsTo('GoodsSku', 'spec_sku_id', 'spec_sku_id', 'spec_sku_id');
	}

	// 关联订单主表
	public function orderM()
	{
		return $this->belongsTo('Order');
	}
}