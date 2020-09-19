<?php
 namespace app\admin\controller;
use think\Controller;
class Advert extends controller{

	public function __construct(){
			parent::__construct();
	}

	public function index(){
		$type = intval(input("param.type",0));
		$where = [];
		if($type != 0){
			$where = ["type"=>$type];
		}
		$types = ["轮播","经典",];
		$list = model("Advert")->where($where)->select();
		foreach($list as &$val){
			$val["type"] = $types[$val["type"] - 1];
			$val["add_time"] = date("Y-m-d H:i",$val['add_time']);
			$val['file'] = fileUrl($val['file']);
		}
		$this->assign("list",$list);
		$this->assign("type",$type);
		return $this->fetch('advert/index');
	}

	//添加
	public function save(){
		$image_ids   = request()->file('img');
		$path = 'uploads/advert/';
		$image_ids   = request()->file('img');

		if($image_ids){
			$info = $image_ids->move(ROOT_PATH . 'public' . DS . $path);
			if(!$info){
				return json_encode(["code"=>400,"msg"=>$image_ids->getError()]);
			}
			$param = input("post.");
			$data["file"] = $path . $info->getSaveName();;
			$data["link"] = $param["link"];
			$data["add_time"] = time();
			$data["type"] = intval($param["type"]);
			$rs = model("Advert")->save($data);
			if($rs){
				return json_encode(["code"=>200,"msg"=>"上传成功"]);
			}else{
				return json_encode(["code"=>400,"msg"=>"上传失败"]);
			}
		}
	}

	//修改
	public function update(){
		$image_ids   = request()->file('img');
		$path = 'uploads/advert/';
		$image_ids   = request()->file('img');
		
		if($image_ids){
			$info = $image_ids->move(ROOT_PATH . 'public' . DS . $path);
			if(!$info){
				return json_encode(["code"=>400,"msg"=>$image_ids->getError()]);
			}
			$param = input("post.");
			$id = $param['id'];
			$data["file"] = $path.$info->getSaveName();
			$data["link"] = $param["link"];
			$data["add_time"] = time();
			 
			$res = model("Advert");
			$mod = $res->where('id='.$id)->field('file,link,add_time')->find();
			$rs = $res->where('id='.$id)->update($data);
			if(!empty($data["file"])){
				unlink($mod['file']);
			}
			if($rs){
				return json_encode(["code"=>200,"msg"=>"修改成功"]);
			}else{
				return json_encode(["code"=>400,"msg"=>"修改失败"]);
			}
		}
	}

	public function del(){
		$id = input("param.id");
		$rs = model("Advert")->where(["id"=>$id])->delete();
		if(!$rs){
			return json(array('code'=>400,'msg'=>'删除失败'));
		}else{
			return json(array('code'=>200,'msg'=>'删除成功'));
		}
	}

}