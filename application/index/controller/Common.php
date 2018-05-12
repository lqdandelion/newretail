<?php
/**
 * Created by PhpStorm.
 * User: lvqian
 * Date: 2018/5/8
 * Time: 16:58
 */
namespace app\index\controller;
use think\Controller;

class Common extends Controller{
    public function index(){
        return $this->fetch();
    }
    public function header(){
        error_log('header is in',3,'../../log');
        return view('public/header');
    }
    public function left(){
        return view('public/left');
    }
}