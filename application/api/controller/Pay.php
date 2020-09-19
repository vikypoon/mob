<?php
namespace app\api\controller;
use app\api\controller\Controller;
use wxgzhpay\data\JsApiPay;
use wxpay\data\WxPayUnifiedOrder;
use wxpay\data\WxPayDataBase;
use wxpay\PayNotifyCallBack;
use wxpay\WxPayApi;
use wxpay\WxPayConfig;
/**
* 
*/
Class Pay extends Controller{

	public function wxpay()
	{
                $openId = "ogRmqv32iixIaFS4sqive7LxBsX8";
        	$input = new \wxgzhpay\data\WxPayUnifiedOrder();
        	$tools = new JsApiPay();
                $input->SetBody("lishi");
                $input->SetAttach("lishi");
                $input->SetOut_trade_no(\wxgzhpay\WxPayConfig::MCHID . date("YmdHis"));
                $input->SetTotal_fee(1);//这个
                $input->SetTime_start(date("YmdHis"));
                $input->SetTime_expire(date("YmdHis", time() + 600));
        //        $input->SetGoods_tag("test");
        //        $input->SetNotify_url("http://www.source.com/api.php/Example/notify");
                $input->SetNotify_url("http://www.yemey.cn/xiangqing/2019322/666/Notify.php");
                $input->SetTrade_type("JSAPI");
                $input->SetOpenid($openId);
                $order = \wxgzhpay\WxPayApi::unifiedOrder($input);
                print_r($order);
                echo "<hr/>";
                $jsApiParameters = $tools->GetJsApiParameters($order);
                // print_r($jsApiParameters);die;
                // echo "<hr/>";
        //        GetOut_trade_no();
                //重新获取签名
                $js_info = json_decode($jsApiParameters);
                $js_data['appId'] = $js_info->appId;
                $js_data['nonceStr'] = $js_info->nonceStr;
                $js_data['package'] = $js_info->package;
                $js_data['signType'] = $js_info->signType;
                $js_data['paySign'] = $js_info->paySign;
                $js_data['timeStamp'] = $js_info->timeStamp;
                print_r($js_data);die;
                return json_encode(['data' => $js_data]);
        
	}

	public function dai_order_weixin_notify()
	{

	}
}
