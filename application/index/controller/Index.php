<?php
namespace app\index\controller;

class Index extends Base
{
    public function index()
    {	
    	$ad = model('advert')->select()->toArray();
    	foreach ($ad as $key => &$val) {
    		$val['file'] = fileUrl($val['file']);
    	}
    	$this->assign('ad',$ad);
        return $this->fetch('index/index');
    }
    public function chat()
    {	
    	$fromid = input('fromid');
    	$toid = input('toid');
    	$this->assign('fromid',$fromid);
    	$this->assign('toid',$toid);
    	return $this->fetch();
    }


    public function ex(){
    	return $this->fetch();
    }

    public function impcode(){
        // $this->_user = is_login();
        // empty($this->_user) && $this->redirect("Admin/login");
        $this->uid = 1;
        $file = request()->file('import');
         if(empty($file)){
            $this->error('请先选择文件');
        }
        // 移动到框架应用根目录/uploads/excel 目录下
        $info = $file->validate(['ext'=>'xls', 'xlsx', 'csv'])->move(ROOT_PATH . 'uploads' . DS . 'excel');
        if($info){
            // 成功上传后 获取上传信息
            // 输出 775729d0f35f32240f541ae17d78a455.xls
            $fileName = $info->getFilename();
              //调用impcodeinfo方法
            $this->impcodeinfo($fileName,$this->uid);
        }else{
            // 上传失败获取错误信息
            echo $file->getError();
        }
    }

public function impcodeinfo($fileName,$id=0){
        header("content-type:text/html;charset=utf-8");

        vendor("phpexcel.PHPExcel");
        vendor("phpexcel.PHPExcel.IOFactory");
        vendor("phpexcel.PHPExcel.Reader.Excel5");
        //文件路径（因为我的文件默认加了一层日期：../uploads/excel/20180409/775729d0f35f32240f541ae17d78a455.xls）
        $filePath = '../uploads/excel/' .date('Ymd').'/'. $fileName ;
        //实例化PHPExcel类
        $PHPExcel = new \PHPExcel();
        //默认用excel2007读取excel，若格式不对，则用之前的版本进行读取
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                echo 'no Excel';
                return;
            }
        }
        //读取Excel文件
        $PHPExcel = $PHPReader->load($filePath);
        //读取excel文件中的第一个工作表
        $sheet = $PHPExcel->getSheet(0);
        //取得最大的列号
        $allColumn = $sheet->getHighestColumn();
        //取得最大的行号
        $allRow = $sheet->getHighestRow();
        //从第二行开始插入,第一行是列名
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            //获取B列的值
            // $department = $PHPExcel->getActiveSheet()->getCell("A" . $currentRow)->getValue();
            //获取C列的值
            $department = $PHPExcel->getActiveSheet()->getCell("B" . $currentRow)->getValue();
            $Invitation = $PHPExcel->getActiveSheet()->getCell("C" . $currentRow)->getValue();
            //获取D列的值
            $shoper = $PHPExcel->getActiveSheet()->getCell("D" . $currentRow)->getValue();
            $do_notdo = $PHPExcel->getActiveSheet()->getCell("E" . $currentRow)->getValue();
            $receive = $PHPExcel->getActiveSheet()->getCell("F" . $currentRow)->getValue();
            $m = db('codeinfo');
            $num = $m->insert(array('department' => $department, 'invitation' => $Invitation, 'shoper' => $shoper,'do_notdo'=>$do_notdo,'receive'=>$receive,'create_time'=>time()));
        }
        if ($num > 0) {
            $this->success('导入成功', url('ex'));
        } else {
            $this->error('导入失败');
        }
    }




}
