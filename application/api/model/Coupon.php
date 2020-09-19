<?php
namespace app\api\model;

use app\common\model\Coupon as CouponModel;

class Coupon extends CouponModel
{
	
	public function add($data)
	{	
		if ($data['expire_type'] == '20') {
			$data['start_time'] = strtotime($data['start_time']);
			$data['end_time']   = strtotime($data['end_time']);
		}
		return $this->allowField(true)->save($data);
	}
}