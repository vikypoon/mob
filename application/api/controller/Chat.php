<?php
namespace app\api\controller;
use think\Controller;
use think\Request;
use think\Db;
/**
* 
*/
class Chat extends Controller
{
	
	public function save_message(){
		if (Request::instance()->isAjax()) {
			$message = input('post.');
			$datas['fromid'] = $message['fromid'];
			$datas['fromname'] = $this->getName($message['fromid']);
			$datas['toid'] = $message['toid'];
			$datas['toname'] = $this->getName($message['toid']);
			$datas['content'] = $message['data'];
			$datas['time'] = $message['time'];
			$datas['isread'] = $message['isread'];
			$datas['type'] = 1;
			Db::name('chat_communication')->insert($datas);
		}
	}


	public function getName($uid){
		$userinfo = Db::name("chat_user")->where('id',$uid)->field('nickname')->find();
		return $userinfo['nickname'];
	}

	/*获取用户头像*/

	public function get_head(){
		if(Request::instance()->isAjax()){
			$fromid = input('fromid');
			$toid = input('toid');
			$frominfo =Db::name("chat_user")->where('id',$fromid)->field('headimgurl')->find();
			$toinfo =Db::name("chat_user")->where('id',$toid)->field('headimgurl')->find();
			return[
				'from_head' =>$frominfo['headimgurl'],
				'to_head' =>$toinfo['headimgurl']
			];


		}
	}

	/*获取用户名称*/

	public function get_name(){

		if(Request::instance()->isAjax()){
			$uid = input('uid');

			$toinfo =Db::name("chat_user")->where('id',$uid)->field('nickname')->find();
			return[
				'to_name' =>$toinfo['nickname']
			];

		}
	}


	/*获取用户聊天记录*/

	public function load(){

			if(Request::instance()->isAjax()){
				$fromid = input('fromid');
				$toid = input('toid');

				$count =Db::name("chat_communication")
						->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])
						->field('fromid,content')
						->count('id'); //:***的方式是占位符,参考http://blog.51cto.com/wujuxiang/403679

				if($count>=10){
				$message = Db::name("chat_communication")
							->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])
							->field('fromid,content')
							->limit($count-10,10)
							->order('id')
							->select(); //:***的方式是占位符,参考http://blog.51cto.com/wujuxiang/403679}
				return $message;
				}else{
				$message =Db::name("chat_communication")
							->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])
							->field('fromid,content')
							->order('id')
							->select(); //:***的方式是占位符,参考http://blog.51cto.com/wujuxiang/403679}
				return $message;
			}
		}

	}	
}