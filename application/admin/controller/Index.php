<?php
namespace app\admin\controller;
use think\Controller;
use think\db\Query;
class Index extends Controller
{
    public function index()
    {	
        return $this->fetch();
    }

    public function welcome()
    {
    	return $this->fetch();
    }

    public function unicode()
    {
    	return $this->fetch();
    }

    public function a()
    {
        return $this->fetch();
    }

    public function register()
    {
        return $this->fetch();
    }

    public function search()
    {
        return $this->fetch();
    }

    public function advList()
    {   
        $type = intval(input("param.type",0));
        $where = [];
        if($type != 0){
            $where = ["type"=>$type];
        }
        $types = ["轮播","经典",];
        $list = model("advert")->where($where)->select();
        // print_r($list);die;
        foreach($list as &$val){
            $val["type"] = $types[$val["type"]];
            $val["add_time"] = date("Y-m-d H:i",$val['add_time']);
            $val['file'] = fileUrl($val['file']);
        }
        $this->assign("list",$list);
        $this->assign("type",$type);
        return $this->fetch();
    }

    public function addData()
    {
        @$conn=mysql_connect("localhost","root","root");  
        if(!$conn){  
            echo "connect failed";  
            exit;  
        } 
        @mysql_select_db("big",$conn); 
        @mysql_query("set names utf8");
         
        $price=10;
        $user_id=1;
        $goods_id=1;
        $sku_id=11;
        $number=1;
         
        //生成唯一订单号
        function build_order_no(){
            return date('ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        }
        //记录日志
        function insertLog($event,$type=0){
            global $conn;
            $sql="insert into ih_log(event,type) 
            values('$event','$type')";  
            @mysql_query($sql,$conn);  
        }
         
        //模拟下单操作
        //库存是否大于0
        mysql_query("BEGIN");   //开始事务
        $sql="select number from ih_store where goods_id='$goods_id' and sku_id='$sku_id' FOR UPDATE";//此时这条记录被锁住,其它事务必须等待此次事务提交后才能执行
        $rs=mysql_query($sql,$conn);
        $row=mysql_fetch_assoc($rs);
        if($row['number']>0){
            //生成订单 
            $order_sn=build_order_no(); 
            $sql="insert into ih_order(order_sn,user_id,goods_id,sku_id,price) 
            values('$order_sn','$user_id','$goods_id','$sku_id','$price')";  
            $order_rs=mysql_query($sql,$conn); 
            
            //库存减少
            $sql="update ih_store set number=number-{$number} where sku_id='$sku_id'";
            $store_rs=mysql_query($sql,$conn);  
            if(mysql_affected_rows()){  
                insertLog('库存减少成功');
                mysql_query("COMMIT");//事务提交即解锁
            }else{  
                insertLog('库存减少失败');
            }
        }else{
            insertLog('库存不够');
            mysql_query("ROLLBACK");
        }
            }
}
