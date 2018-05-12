<?php
/**
 * Created by PhpStorm.
 * User: lvqian
 * Date: 2018/4/22
 * Time: 8:59
 */

namespace app\admin\controller;
use app\admin\controller\Base;
use think\Controller;
use think\Request;
use think\Db;


class Homepage extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function index()
    {
        $this->checkLogin();
        $this->checkPerfect();
        $this->assign('webtitle',config('web_title'));
        return view('index/index');
    }

    public function toPerfect(){
        $this->checkLogin();
        $this->checkOver();
        $this->assign('webtitle',config('web_title'));
        return view('public/perfect');
    }

    public function doPerfect(){
        $this->assign('webtitle',config('web_title'));
        error_log('shop=' . print_r($_POST,true),3,'../../log');
        $shop = [];
        $shop['shop_name'] = trim($_POST['shop_name']);
        if(!$shop['shop_name']){
            return [
                'msg' => '商户名称不可以为空',
                'data' => FAIL,
            ];
        }
        $uploadFile = picupload();
        error_log('uploadFile = ' . print_r($uploadFile,true),3,'../../log');
        if($uploadFile['data'] == FAIL){
            return $uploadFile;
        }
        $shop['shop_icon'] = $uploadFile['msg'];
        $channel = $_POST['channel'];
        if(!$channel){
            return [
                'msg' => '请选择营业频道',
                'data' => FAIL,
            ];
        }
        $shop['shop_channel'] = config('channelToType')[$channel];
        $address = $_POST['Addr'];
        if(!$address){
            return [
                'msg' => '请选择商家地址',
                'data' => FAIL,
            ];
        }
        $shop['address'] = $address;
        $shop['address_detail'] = $_POST['address_detail'];
        $sendPrice = floatval($_POST['send_price']);
        if($sendPrice<0){
            return [
                'msg' => '起送价为非负数',
                'data' => FAIL,
            ];
        }
        $shop['send_price'] = $sendPrice;
        $delivery = floatval($_POST['delivery']);
        if($delivery<0){
            return [
                'msg' => '配送费为非负数',
                'data' => FAIL,
            ];
        }
        $shop['delivery'] = $delivery;
        $startTime = $_POST['start_time'];
        $endTime = $_POST['end_time'];
        $start = strtotime(date("Y-m-d") . $startTime . ":00");
        $end = strtotime(date('Y-m-d') . $endTime . ":00");
        if($start >= $end){
            return [
                'msg' => '开始时间大于结束时间',
                'data' => FAIL,
            ];
        }
        $shop['start_time'] = $startTime;
        $shop['end_time'] = $endTime;
        $shop['city_id'] = intval($_POST['cityCode']);
        $provinceCode = $_POST['code'];
        $shop['province_id'] = intval(substr($provinceCode,0,2) . "0000");

        $shop['shop_id'] = time() . rand(100,1000);
        $shop['create_time'] = time();
        $shop['update_time'] = time();
        $shop['bus_user_id'] = session('b_id');
        $shop['shop_phone'] = session('b_phone');

        error_log('shop = ' . print_r($shop,true),3,'../../log');
        $uuid = Db::table('nr_shopinfo')->insertGetId($shop);
        if($uuid){
            session('shop_id',$shop['shop_id']);
            return [
                'msg' => $uuid,
                'data' => SUCCESS,
            ];
        }else{
            return [
                'msg' => "完善失败",
                'data' => FAIL,
            ];
        }
    }

    public function toEditPerfect(){
        $this->checkLogin();
        $this->checkPerfect();
        $b_id = session('b_id');
        $fields = "shop_id,shop_name,shop_phone,shop_icon,shop_channel,address,address_detail,send_price,delivery,start_time,end_time";
        $ret = Db::table('nr_shopinfo')
            ->field($fields)
            ->where(['bus_user_id'=>$b_id])
            ->select();
        $ret = array_shift($ret);
        $ret['channel'] = config('typeToChannel')[$ret['shop_channel']];
        error_log('perfect = ' . print_r($ret,true),3,'../../log');
        $this->assign('my',$ret);
        $this->assign('webtitle',config('web_title'));
        return view('index/editPerfect');
    }
    public function editPerfect(){
        error_log('shop=' . print_r($_POST,true),3,'../../log');
        $shop = [];
        $shop['shop_name'] = trim($_POST['shop_name']);
        if(!$shop['shop_name']){
            return [
                'msg' => '商户名称不可以为空',
                'data' => FAIL,
            ];
        }
        $shop['shop_phone'] = $_POST['shop_phone'];
        if(!$shop['shop_phone']){
            return [
                'msg' => '手机号不可以为空',
                'data' => FAIL,
            ];
        }
        if(!checkPhone($shop['shop_phone'])) {
            $result['msg'] = "手机号不合法";
            $result['data'] = FAIL;
            return $result;
        }
        $uploadFile = picupload();
        error_log('uploadFile = ' . print_r($uploadFile,true),3,'../../log');
        if($uploadFile['data'] == FAIL && empty($_POST['img'])){
            return $uploadFile;
        }
        if($uploadFile['data'] == SUCCESS) {
            $shop['shop_icon'] = $uploadFile['msg'];
        }

        $address = $_POST['Addr'];
        if(!$address){
            return [
                'msg' => '请选择商家地址',
                'data' => FAIL,
            ];
        }
        $shop['address'] = $address;
        $shop['address_detail'] = $_POST['address_detail'];
        $sendPrice = floatval($_POST['send_price']);
        if($sendPrice<0){
            return [
                'msg' => '起送价为非负数',
                'data' => FAIL,
            ];
        }
        $shop['send_price'] = $sendPrice;
        $delivery = floatval($_POST['delivery']);
        if($delivery<0){
            return [
                'msg' => '配送费为非负数',
                'data' => FAIL,
            ];
        }
        $shop['delivery'] = $delivery;
        $startTime = $_POST['start_time'];
        $endTime = $_POST['end_time'];
        $start = strtotime(date("Y-m-d") . $startTime . ":00");
        $end = strtotime(date('Y-m-d') . $endTime . ":00");
        if($start >= $end){
            return [
                'msg' => '开始时间大于结束时间',
                'data' => FAIL,
            ];
        }
        $shop['start_time'] = $startTime;
        $shop['end_time'] = $endTime;
        $shop['city_id'] = intval($_POST['cityCode']);
        $provinceCode = $_POST['code'];
        $shop['province_id'] = intval(substr($provinceCode,0,2) . "0000");

        $shop['update_time'] = time();


        error_log('shop = ' . print_r($shop,true),3,'../../log');
        $ret = Db::table('nr_shopinfo')->where(['shop_id' => $_POST['shop_id']])->update($shop);
        if($ret){
            return [
                'msg' => '修改成功',
                'data' => SUCCESS,
            ];
        }else{
            return [
                'msg' => "修改失败",
                'data' => FAIL,
            ];
        }
    }

}