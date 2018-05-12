<?php
/**
 * Created by PhpStorm.
 * User: lvqian
 * Date: 2018/4/13
 * Time: 16:49
 */

namespace app\index\controller;
use think\Controller;

class Login extends Controller{
    public function index(){
        return $this->fetch();
    }
}