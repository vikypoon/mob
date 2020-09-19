<?php
namespace app\common\model;
class User extends BaseModel
{
	public function getUser($open_id)
	{
		$user = (new user)->where(['open_id'=>$open_id])->find();
		// var_dump($user) ;die;
		$user['address'] = (new userAddress)->where(['user_id'=>$user['user_id'],'default_address'=>1])->find()->toArray();
		// $user['address'] = empty($address) ? $address : [];
		// print_r($user['address']);die;
		return $user;
	}
}