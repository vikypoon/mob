<?php
namespace app\api\controller;
use app\common\exception\BaseException;
/**
* 
*/
class Controller extends \think\Controller
{
	

	const JSON_SUCCESS_STATUS = 1;
	const JSON_ERROR_STATUS   = 0;

	public function _initialize()
    {
        // 当前小程序id
        // $this->wxapp_id = $this->getWxappId();
        // // 验证当前小程序状态
        // $this->checkWxapp();
    }

	protected function renderJson($code = self::JSON_SUCCESS_STATUS, $msg = '', $data = []){
		return compact('code', 'msg', 'url', 'data');
	}

	// 返回操作成功json
	protected function renderSuccess($data = [],$msg = 'success'){
		return $this->renderJson(self::JSON_SUCCESS_STATUS, $msg, $data);
	}

	// 返回操作失败json
	protected function renderError($data = [], $msg = 'error'){
		return $this->renderJson(self::JSON_ERROR_STATUS, $msg, $data);
	}

	protected function getUser($is_force = true)
	{
		// if (!$token = $this->request->param('token')) {
		// 	$is_force && $this->throwError('缺少必要的参数：token', -1);
		// 	return false;
		// }
		// if (!$user = UserModel::getUser($token)) {
		// 	$is_force && $this->throwError('没有找到用户信息', -1);
		// 	return false;
		// }
		$user = 1;
		return $user;
	}

	protected function throwError($msg, $code = 0)
    {
        throw new BaseException(['code' => $code, 'msg' => $msg]);
    }

}