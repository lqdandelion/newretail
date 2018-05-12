
<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

use think\Request;
use think\Session;
use think\Controller;

//获取当前域名
function getdomain(){
    $request = Request::instance();
    //获取当前域名  携带https 或 http
    $url_https_wshx=$request->domain();
    return $url_https_wshx;
}

function picupload($id = 0){
    //判断文件上传是否出错
    $result = [];
    $result['data']=0;
    if(empty($_FILES["file"])){
        error_log('file is null',3,'../../log');
        $_FILES['file'] = $_FILES['lastfile'];
    }
    if($_FILES['file']['error'] == 4 && isset($_FILES['lastfile'])){
        $_FILES['file'] = $_FILES['lastfile'];
    }

    if($_FILES["file"]["error"]==4)
    {

        $result['msg'] = $_FILES["file"]["error"];
        $result['data']=1;
    }
    else
    {
        //控制上传的文件类型，大小
        if($_FILES["file"]["type"]=="image/jpeg"||$_FILES["file"]["type"]=="image/jpg" && $_FILES["file"]["type"]=="image/png"&&$_FILES["file"]["size"]<1024000)
        {
            //找到文件存放位置，注意tp5框架的相对路径前面不用/
            //这里的filename进行了拼接，前面是路径，后面从date开始是文件名
            //我在static文件下新建了一个file文件用来存放文件，要注意自己建一个文件才能存放传过来的文件
            if($id == 0) {
                $filename = "static/upload/" . date("YmdHis") . $_FILES["file"]["name"];
            }else{
                $filename = "static/upload/" . $id . $_FILES["file"]["name"];
            }
            //判断文件是否存在
            if (file_exists($filename))
            {
                $result['msg'] = $filename;
                $result['data'] = 0;
            }
            else
            {
                //保存文件
                //move_uploaded_file是php自带的函数，前面是旧的路径，后面是新的路径
                move_uploaded_file($_FILES["file"]["tmp_name"],$filename);
                $result['msg'] = $filename;
                $result['data']=0;
            }
        }
        else
        {
            $result['msg'] =  "文件类型不正确！";
            $result['data']=1;
        }
    }
    return $result;
}

function checkPhone($phone){
    if(preg_match("/^1[34578]\d{9}$/", $phone)){
        return true;
    }
    return false;
}

function checkPassword($password){
    if(preg_match("/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,10}$/", $password)){
        return true;
    }
    return false;
}




