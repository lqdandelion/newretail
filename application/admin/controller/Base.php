<?php
/**
 * Created by PhpStorm.
 * User: lvqian
 * Date: 2018/4/27
 * Time: 17:02
 */

namespace app\admin\controller;

use think\Controller;
use think\db;
use think\Request;
use think\response;

class Base extends Controller{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function checkLogin(){
        if(!session('b_id')){
            $this->error('还未登录，即将跳转','config/tologin');
            //$this->redirect('config/tologin');
        }
    }
    public function checkPerfect(){
        if(!session('shop_id')){
            $this->error('商户资料未完善，即将跳转','homepage/toperfect');
            //$this->redirect('homepage/toperfect');
        }
    }
    public function checkOver(){
        if(session('shop_id')){
            $this->redirect('homepage/index');
        }
    }
    public function clearSession(){
        session('b_id',null);
        session('shop_id',null);
    }
}