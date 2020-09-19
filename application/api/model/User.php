<?php
namespace app\api\model;

use app\common\model\User as UserModel;

class User extends UserModel
{
	
	public function getList($nickName = '',$gender = null)
	{
		!empty($nickName) && $this->where('nickName','like',"%$nickName%");
		$gender > -1 && $this->where('gender','=',(int)$gender);
		return $this->where('is_delete','=','0')
			->order(['create_time' => 'desc'])
			->paginate(10,false,[
				'query' => \request()->request()
				]);
	}
}