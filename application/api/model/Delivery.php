<?php
namespace app\api\model;

use app\common\model\Delivery as DeliveryModel;

class Delivery extends DeliveryModel
{
	
	public function checkAddress($city_id)
	{
		$cityIds = explode(',', implode(',', array_column($this['rule']->toArray(), 'region')));
		return in_array($city_id, $cityIds);
		// $cityids = 
	}
}