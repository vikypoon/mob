<?php
namespace app\admin\controller;
use think\Controller;

class Base extends Controller
{
	
	public function __construct()
	{
		// parent::__construct();
		// if (!session('admin_id')) {
		// 	$this->redirect('login/index',302);
		// }
	}
}