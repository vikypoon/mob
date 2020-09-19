<?php
namespace app\common\model;
use think\Request;
use think\Db;
Class DeliveryRule extends BaseModel
{
	public function checkAdress($delivery_id, $cityId)
	{
		$deliveryIds = (new deliveryRule)->field('region')->where(['delivery_id'=>$delivery_id])->find()->toArray();
		$ids = explode(',', implode(',', $deliveryIds));
		if (in_array($cityId, $ids)) {
			return true;
		}else{
			return false;
		}
	}
}