<?php
namespace app\common\model;
use think\Request;
use think\Db;
use app\common\model\goodsSku;
class Goods extends BaseModel
{	
	protected $name = 'goods';
    // protected $append = ['goods_sales'];

	public function category()
	{
		return $this->belongsTo('Category');
	}

	public function image()
	{
		return $this->hasmany('GoodsImage')->order(['id' => 'asc']);
	}

	public function sku()
	{
		return $this->hasmany('GoodsSku');
	}

	public function specRel()
    {
        return $this->belongsToMany('SpecValue', 'GoodsSpecRel');
    }

    public function delivery()
    {
        return $this->BelongsTo('Delivery');
    }

    public function commentData()
    {
        return $this->hasMany('Comment');
    }

    public function spec()
    {
    	return $this->belongsTo('Spec');
    }

    public function user()
    {
    	return $this->belongsTo('User');
    }

    public  function getManySpecData($spec_rel,$sku)
    {   

    	$specAttrData = [];
    	foreach ($spec_rel->toArray() as $item) {
    		print_r($item);die;
    		if (isset($item['spec_id'])) {
    			echo 666;die;
    		}
    		echo 888;die;
    	}
    	

    }

    //商品列表
    public function getList($status = null, $category_id = 0, $search = '', $sortType = 'all', $sortPrice = false, $listRows = 15)
    {
        //排序规则
        $sort = [];
        if ($sortType === 'all') {
            $sort = ['goods_sort','goods_id' => 'desc'];
        }elseif ($sortType === 'sales') {
            $sort = ['goods_sales' => 'desc'];
        }elseif ($sortType === 'price') {
            $sort = $sortPrice ? ['goods_max_price' => 'desc'] : ['goods_min_price'];
        }
        //商品表名
        $tableName = $this->getTable();
        // print_r($tableName);
        //多规格商品 最高价与最低价
        $GoodsSku = new GoodsSku;
        $minPriceSql = $GoodsSku->field(['MIN(goods_price)'])
            ->where('goods_id', 'EXP', "= `$tableName`.`goods_id`")->buildSql();
        $maxPriceSql = $GoodsSku->field(['MAX(goods_price)'])
            ->where('goods_id', 'EXP', "= `$tableName`.`goods_id`")->buildSql();
        $list = $this ->field(['*', '(sales_initial + sales_actual) as goods_sales',
             "$minPriceSql AS goods_min_price",
             "$maxPriceSql AS goods_max_price"])
            ->with(['category', 'sku'])
            ->where('is_delete', '=', 0)
            // ->where($filter)
            ->order($sort)
            ->paginate($listRows, false, [
                'query' => Request::instance()->request()
                ]);
        return $list;

    }

    

	// 商品详情
	public static function detail($goods_id)
	{
		$model = new static;
		$rs = $model
		->with([
			'category',
            'image',
            'sku',
            'spec_rel',
            // 'spec',
            'delivery',
            'commentData'=>function($query){
            	$query->with('')->where(['is_delete'=>0, 'status'=>1])->limit(2);
            }
            ])
		->withCount(['commentData'=>function ($query) {$query->where(['is_delete'=>0, 'status'=>1]);}
			])
		->where('goods_id', '=', $goods_id)->find()->toArray();
		return $rs;
	}

	//获取sku
	public  function getGoodsSku($goods_sku_id)
	{	
		$sku = $this['spec_rel']->toArray();
		print_r($sku);die;
		if (!isset($goodsSkuData[$goods_sku_id])) {
            return false;
        }
        $goods_sku = $goodsSkuData[$goods_sku_id];
        // 多规格文字内容
        $goods_sku['goods_attr'] = '';
        if ($this['spec_type'] === 20) {
            $attrs = explode('_', $goods_sku['spec_sku_id']);
            $spec_rel = array_column($this['spec_rel']->toArray(), null, 'spec_value_id');
            foreach ($attrs as $specValueId) {
                $goods_sku['goods_attr'] .= $spec_rel[$specValueId]['spec']['spec_name'] . ':'
                    . $spec_rel[$specValueId]['spec_value'] . '; ';
            }
        }
        return $goods_sku;
	}


    public function goodsSku($goods_sku_id)
    {
        $sku = new GoodsSku;
        return $sku->where('goods_sku_id', '=',$goods_sku_id)->find()->toArray();
       
    }

    // 商品状态
    public function goodsInfo($goods_id)
    {   
        $goods = new Goods;
        return $goods->where(['goods_id' =>$goods_id])->find()->toArray();
    }
}