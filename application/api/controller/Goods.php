<?php
namespace app\api\controller;
use think\Controller;
use think\Request;
use think\Db;
// use app\api\model\Goods as GoodsModel;
use app\common\model\Goods as GoodsModel;
/**
* 
*/
class Goods extends Controller
{	
	public function detail()
	{	
		$goods_id = 1;
		$goods = GoodsModel::detail($goods_id);
		// print_r($model);
		return $goods;
		
	}

	public function getList()
	{
		$model = new  GoodsModel;
		$list = $model->getList()->toArray();
		$goodsList = json_encode(['code' => 200, 'msg' => 'success', 'data' => $list],JSON_UNESCAPED_UNICODE);
		 return $goodsList;
		
	}

	public function burl(){
		$url = $this->base_url();
		print_r($url);
	}

	//获取当前url
	function base_url()
	{
	    $request = Request::instance();
	    $subDir = str_replace('\\', '/', dirname($request->server('PHP_SELF')));
	    return $request->scheme() . '://' . $request->host() . $subDir . ($subDir === '/' ? '' : '/');
	}


	// 微信红包
	public function hong()
	{
		$total=19.5;//红包总金额 
		$num=10;// 分成 10 个红包，支持 10 人随机领取
		$min=0.01;//每个人最少能收到 0.01 元
		$money_arr=array(); //存入随机红包金额结果
		for ($i=1;$i<$num;$i++) {
			 $safe_total=($total-($num-$i)*$min)/($num-$i);//随机安全上限
			 dump($safe_total);
			 $money= mt_rand($min*100,$safe_total*100)/100;
			 dump($money);
			 $total=$total-$money; $money_arr[]= $money; 
			 echo '第'.$i.'个红包：'.$money.' 元，余额：'.$total.' 元 '."<br/>";
		 }
		    echo '第'.$num.'个红包：'.round($total,2).' 元，余额：0 元'; 
		    $money_arr[] = round($total,2);
		    // dump($money_arr);
	}
	
}