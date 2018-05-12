<?php
namespace app\index\controller;
use think\Request;
use think\Controller;
use think\Db;


class Index extends Controller
{
    public function index()
    {
        $request = Request::instance();
        $ip = $request->ip();
        $url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=' . $ip;
        //$urlTaobao = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$ip;
        $data = file_get_contents($url);
        $data = json_decode($data, true);
        $city =  $data['city'];
        $this->assign('nowcity',$city);
        $cityCode = Db::table('nr_city')->field('city_id')->where(['city_name'=>$city])->select();
        error_log('city_code = ' . print_r($cityCode,true),3,'../../log');
        $shop = [];
        if($cityCode) {
            $cityCode = $cityCode[0]['city_id'];
            $shop = Db::table('nr_shopinfo')
                ->field('shop_id,shop_name,shop_phone,shop_icon,send_price,delivery,start_time,end_time')
                ->where(['city_id'=>$cityCode])
                ->order('create_time desc')
                ->limit(10)
                ->select();
        }
        $this->assign('shop',$shop);
        error_log('shop = ' . print_r($shop,true),3,'../../log');
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
        return view('index/index');
    }
    public function test(){
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
        return view('index/test');
    }
}
