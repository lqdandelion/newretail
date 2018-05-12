<?php
namespace app\admin\controller;


use app\admin\controller\Base;
use think\Controller;

class Index extends Controller
{
    public function index()
    {

        $this->assign('webtitle',config('web_title'));
        return view('public/login');
    }
    public function test(){
        $this->assign('webtitle',config('web_title'));
        return view('public/test5');
    }
    public function check()
    {
        $captcha = input('verify');
        if (!captcha_check($captcha)) {
            $this->error('错误');
        } else {
            $this->success('正确');
        }
    }
}
