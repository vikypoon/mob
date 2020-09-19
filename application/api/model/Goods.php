<?php
namespace app\api\model;
use app\common\model\Goods as GoodsModel;
use think\Model;
class Goods extends GoodsModel
{

	public function getListByIds($goodsIds, $status = null)
    {
        $filter = ['goods_id' => ['in', $goodsIds]];
        $status > 0 && $filter['goods_status'] = $status;
        return $this->with(['category', 'image.file', 'sku', 'spec_rel.spec', 'delivery.rule'])
            ->where($filter)
            ->select();
    }
}

