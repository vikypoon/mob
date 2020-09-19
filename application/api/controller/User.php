<?php
namespace app\api\controller;
use think\Controller;
use think\Request;
use think\Db;
use app\api\model\User as Usermodel;
/**
* 
*/
class User extends Controller
{
	
	public function login(){

		$model = new Usermodel;
		return $this->renderSuccess([
				'user_id' => $model->login($this->request->post()),
				'token'   => ''
			]);
	}

	// 用户列表or查询
	public function getList()
	{	
		$nickName = '';
		$gender = null;
		$model = new Usermodel;
		$list  = $model->getList($nickName,$gender);
		return compact('list');
	}


}