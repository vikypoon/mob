<?php
namespace app\admin\model;

use app\common\model\Goods as GoodsModel;
use think\Db;

// 添加商品
class Goods extends GoodsModel{
	public function add(array $data)
	{	
		// if (empty($data['images'])) {
		// 	$this->error = "请上传商品图片";
		// 	return false;
		// }
		$data['create_time'] = time();
		$data['update_time'] = time();

		// 开始事物
		Db::startTrans();
		try{
		    $goods = $this->allowField(true)->save($data);
		    $data['sku']['goods_id'] = $this->goods_id;
		    $this->addGoodsSpec($data);
		    // 提交事务
		    Db::commit();    
		} catch (\Exception $e) {
		    // 回滚事务
		     return $e->getMessage();
		    Db::rollback();
		}
	}

	// 添加规格
	public function addGoodsSpec($data)
	{	
		$model = new GoodsSku;
		if ($data['spec_type'] === '10') {
		    // var_dump($data['sku']);die;
			$model->allowField(true)->save($data['sku']);
		}else if($data['spec_type'] === '20'){
			echo '你来了';
		}
	}
}