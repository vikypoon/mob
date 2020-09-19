<?php
namespace app\common\model;
use think\model;
class Category extends BaseModel
{
	public function goods()
	{
		return $this->hasMany('Goods');
	}

	public static function cateList($category_id)
	{	
		$model = new static;
		return $model->with('goods')->where(['category_id'=> $category_id])->select();
	}
}