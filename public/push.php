<?php
$conn=mysqli_connect("localhost","root","root");  
if(!$conn){  
	echo "connect failed";  
	exit;  
} 
mysqli_select_db("mob",$conn); 
mysqli_query("set names utf8");
$redis = new Redis();
$redis->connect('127.0.0.1',6379);
$redis_name = 'miaosha';
// $db = DB::getlntance();

while ( 1 ) {
	$user = $redis->lPop($redis_name);
	if (!$user || $user == 'nil') {
		sleep(2);
		continue;
	}
	//切割时间
	$user_arr = explode('%',$user);
	$insert_data = array(
		'uid' => $user_arr[0],
		'time_stamp' => $user_arr[1],
		);
	// 保存到数据库
	$res = $conn ->insert('ye_redis_queue',$insert_data);

	//数据库插入失败的时候回滚机制
	if (!$res) {
		$redis->rPush($redis_name,$user);
	}

	sleep(2);
}
// 释放一下redis
$redis->close();

