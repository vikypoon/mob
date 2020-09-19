<?php
namespace app\admin\controller;
use think\Controller;
class Login extends Controller
{
    public function index()
    {	
        return $this->fetch();
    }

    public function login()
    {
    	$params = input('post.');
        $username = isset($params['username']) ? trim($params['username']) : "";
        $passwd = isset($params['password']) ? trim($params['password']) : "";
        $admin = model('admin')->field('id,username,password')->where(['username'=>$username])->find()->toArray();
        // print_r($admin);die;
        if ($admin) {
            if (md5($passwd) == $admin['password']) {
                session('admin_id',$admin['id']);
                session('admin_name',$admin['username']);
                session('admin',$admin);
                unset($admin['password']);
                return json_encode(['code'=>200,'msg'=>'登录成功'],JSON_UNESCAPED_UNICODE);
            }else{
                return json_encode(['code'=>400,'msg'=>'密码错误'],JSON_UNESCAPED_UNICODE);
            }
        }else{
            return json_encode(['code'=>400,'msg'=>'账号不存在'],JSON_UNESCAPED_UNICODE);
        }
    }

    public function logout()
    {
        session(null);
        $this->redirect('login/index',302);
    }

    public function test()
    {   var_dump(session('admin'));
    	return $this->fetch();
    }
}
