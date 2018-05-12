<?php
/**
 * Created by PhpStorm.
 * User: lvqian
 * Date: 2018/4/27
 * Time: 17:02
 */

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\response;
use Aliyun\DySDKLite\Sms;
use think\cache\driver\Redis;

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
    public function loadCity(){
        $request = Request::instance();
        $ip = $request->ip();
        $url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=' . $ip;
        //$urlTaobao = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$ip;
        $data = file_get_contents($url);
        $data = json_decode($data, true);
        $city =  $data['city'];
        $this->assign('nowcity',$city);
        error_log('----------------here is load---------------',3,'../../log');
        $cityInfo = Db::table('nr_city')->field('city_id,city_name,city_pinyin,city_jc')->select();
        $one = [];
        $two = [];
        $three = [];
        $four = [];
        $five = [];
        $six = [];
        $city = [];
        foreach ($cityInfo as $key => $value){
            $city[] = array_values($value);
            $fir = substr($value['city_pinyin'],0,1);
            if(in_array($fir,['a','b','c','d','A','B','C','D'])){
                array_push($one,$key);
            }
            else if(in_array($fir,['e','E','f','g','h','i','F','G','H','I'])){
                array_push($two,$key);
            }
            else if(in_array($fir,['j','J','k','l','m','K','L','M'])){
                array_push($three,$key);
            }else if(in_array($fir,['n','N','o','O','p','q','r','P','Q','R'])){
                array_push($four,$key);
            }else if(in_array($fir,['s','S','t','T','u','U','v','V','W','w'])) {
                array_push($five,$key);
            }
            else {
                array_push($six,$key);
            }
        }
        $this->assign('city',json_encode($city));
        $this->assign('one',json_encode($one));
        $this->assign('two',json_encode($two));
        $this->assign('three',json_encode($three));
        $this->assign('four',json_encode($four));
        $this->assign('five',json_encode($five));
        $this->assign('six',json_encode($six));
    }
}