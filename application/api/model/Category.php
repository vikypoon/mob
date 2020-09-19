<?php
namespace app\api\model;

use app\common\model\Category as CategoryModel;
use think\Model;
class Category extends CategoryModel
{
	protected $hidden =[
		'wxapp_id'
	];

	public function allCategory()
	{
		$model = new static;
		$cate = $model->order(['sort' => 'asc','create_time' => 'asc'])->select();
		$all = !empty($cate) ? $cate->toArray() : [];
		$tree = [];
		foreach ($all as  $first) {
			if($first['parent_id'] !== 0) continue;
		// print_r($first);die;
			$twoTree = [];
			foreach ($all as $two) {
				if($two['parent_id'] !== $first['category_id']) continue;
				$threreThree = [];
				// print_r($two);
				foreach ($all as $three) {
					$three['parent_id'] == $two['category_id'] && $threreThree[$three['category_id']] == $three;
					// print_r($three);die;
					!empty($threreThree) && $two['child'] == $threreThree;
					$twoTree[$two['category_id']] = $two;
				}
				if (!empty($twoTree)) {
                    array_multisort(array_column($twoTree, 'sort'), SORT_ASC, $twoTree);
                    $first['child'] = $twoTree;
                }
                $tree[$first['category_id']] = $first;

			}

		}
		return $tree;
	}
}

