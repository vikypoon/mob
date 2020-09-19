<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\model\Goods as GoodsModel;
class Goods extends Controller{
	public function add(){
		$input = input('post.');
		$model = new GoodsModel;
		$add = $model->add($input);
		if ($add) {
			return json_encode(['code' => 200,'msg' => '添加成功'],JSON_UNESCAPED_UNICODE);
		}else{
			return json_encode(['code' => 400,'msg' => '哼'],JSON_UNESCAPED_UNICODE);
		}


	}
}