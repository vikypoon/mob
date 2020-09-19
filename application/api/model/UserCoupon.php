<?php
namespace app\api\model;
use think\Db;
use app\common\model\UserCoupon as UserCouponModel;
class UserCoupon extends UserCouponModel
{	
	public function getUserCouponList($user_id, $orderPayPrice)
	{
		$list = (new self)->getList($user_id);
		// print_r($list);die;
		$data = [];
		foreach ($list as  $coupon) {
			// 最低消费金额
			// if ($orderPayPrice > $coupon['min_price']) continue;
			// 有效期范围内
			// if ($coupon['start_time']['value'] > time()) continue;
			$key = $coupon['user_coupon_id'];
			// print_r($coupon['min_price']);die;
			$data[$key] = [
				'user_coupon_id' => $coupon['user_coupon_id'],
                'name' => $coupon['name'],
                'color' => $coupon['color'],
                'coupon_type' => $coupon['coupon_type'],
                'reduce_price' => $coupon['reduce_price'],
                'discount' => $coupon['discount'],
                'min_price' => $coupon['min_price'],
                'expire_type' => $coupon['expire_type'],
                'start_time' => $coupon['start_time'],
                'end_time' => $coupon['end_time'],
			];
			// 计算打折金额
			if ($coupon['coupon_type']['value'] === 20) {
				$reuduce_price = $orderPayPrice * ($coupon['discount'] / 10);
			}else{
				$data[$key]['reduce_price'] = $coupon['reduce_price'];
			}
		}
		return array_sort($data, 'reduce_price', true);
	}


	public function getList($user_id, $is_use = false, $is_expire = false)
	{
		return $this->where('user_id', '=', $user_id)
			->where('is_use', '=', $is_use ? 1 : 0)
            ->where('is_expire', '=', $is_expire ? 1 : 0)
			->select()->toArray();
	}

	 /**
     * 设置优惠券为已使用
     * @return false|int
     */
    public function setIsUse()
    {
        return $this->save(['is_use' => 1]);
    }
}