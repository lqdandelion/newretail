<?php
/**
 * Created by PhpStorm.
 * User: lvqian
 * Date: 2018/4/13
 * Time: 16:49
 */

namespace app\index\controller;
use think\Controller;
use Aliyun\DySDKLite\Sms;
use think\Db;
use think\cache\driver\Redis;

class Config extends Base{
    public function getCode(){
        $phone = $_POST['phone'];
        $key = $phone . $_POST['key'];
        if(!checkPhone($phone)) {
            $result['msg'] = "手机号不合法";
            $result['data'] = FAIL;
            return $result;
        }
        $code = rand(100000,999999);
        $note = new \Aliyun\DySDKLite\Sms\Note();
        $res = $note->sendSms($phone,$code);
        //error_log('resNote = ' . print_r($res,true),3,'../../log');
        $name = $_POST['key'] . "-hid-code";
        if($res->Code == 'OK'){
            $redis = new Redis();
            $code = $code;
            $redis->set($key , $code,1800);
            return [
                'data'=>SUCCESS,
                'code' => $code,
                'msg'=>md5($code),
                'name' => $name,
            ];
        }
        return [
            'data'=>FAIL,
            'msg'=>$res->Message,
        ];
    }
    public function doRegister(){
        $user = [];
        $user['username'] = $_POST['username'];
        if(empty($user['username'])){
            return [
                'data' => FAIL,
                'msg' => '用户名不能为空',
                'name' => 'username-error',
            ];
        }
        if(mb_strlen($user['username'])>10){
            return [
                'data' => FAIL,
                'msg' => '用户名过长',
                'name' => 'username-error',
            ];
        }
        $user['phone'] = $_POST['phone'];
        if(empty($user['phone'])){
            return [
                'data' => FAIL,
                'msg' => '手机号不能为空',
                'name' => 'register-phone-error',
            ];
        }
        if(!checkPhone($user['phone'])){
            return [
                'data' => FAIL,
                'msg' => '手机号格式不正确',
                'name' => 'register-phone-error',
            ];
        }
        $code = $_POST['code'];
        $redis = new Redis();
        $key = $user['phone'] . 'register';
        if($redis->get($key) != $code){
            return [
                'data' => FAIL,
                'msg' => '验证码不正确',
                'name' => 'register-code-error',
            ];
        }
        $user['password'] = $_POST['pwd'];
        if(empty($user['password'])){
            return [
                'data' => FAIL,
                'msg' => '密码不能为空',
                'name' => 'register-pwd-error',
            ];
        }
        if(!checkPassword($user['password'])){
            return [
                'data' => FAIL,
                'msg' => '密码不符合要求',
                'name' => 'register-pwd-error',
            ];
        }
        $repwd = $_POST['repwd'];
        if(empty($repwd)){
            return [
                'data' => FAIL,
                'msg' => '确认密码不能为空',
                'name' => 'register-repwd-error',
            ];
        }
        if($user['password'] != $repwd){
            return [
                'data' => FAIL,
                'msg' => '两次输入的密码不一致',
                'name' => 'register-repwd-error',
            ];
        }
        $uuid = Db::table('nr_user')->where(['phone'=>$user['phone']])->select();
        if($uuid){
            return [
                'data' => FAIL,
                'msg' => '该手机号已注册',
                'name' => 'register-phone-error',
            ];
        }
        $user['create_time']=$user['update_time']=$user['last_login_time'] = time();
        $user['password'] = md5($user['password']);
        $ret = Db::table('nr_user')->insertGetId($user);
        if($ret){
            return [
                'data' => SUCCESS,
                'msg' => '注册成功',
            ];
        }else{
            return [
                'data' => FAIL,
                'msg' => '注册失败',
                'name' => '',
            ];
        }
    }

    public function doLogin(){
        $user = [];
        $user['phone'] = $_POST['phone'];
        if(empty($user['phone'])){
            return [
                'data' => FAIL,
                'msg' => '手机号不能为空',
                'name' => 'login-phone-error',
            ];
        }
        $user['password'] = $_POST['pwd'];
        if(empty($user['password'])){
            return [
                'data' => FAIL,
                'msg' => '密码不能为空',
                'name' => 'login-pwd-error',
            ];
        }
        $code = $_POST['code'];
        if(empty($code)){
            return [
                'data' => FAIL,
                'msg' => '验证码不能为空',
                'name' => 'pwd-code-error',
            ];
        }
        if(!captcha_check($code)){
            return [
                'data' => FAIL,
                'msg' => '验证码不正确',
                'name' => 'pwd-code-error',
            ];
        }
        $ret = Db::table('nr_user')->field('username')->where(['phone'=>$user['phone']])->select();
        if(!$ret){
            return [
                'data' => FAIL,
                'msg' => '此用户不存在',
                'name' => 'login-phone-error',
            ];
        }
        $user['password'] = md5($user['password']);
        $ret = Db::table('nr_user')->where($user)->select();
        if($ret){
            Db::table('nr_user')->where(['phone'=>$user['phone']])->update(['last_login_time'=>time()]);
            return [
                'data' => SUCCESS,
                'msg' => '登录成功',
                'username' => $ret[0]['username'],
            ];
        }else{
            return [
                'data' => FAIL,
                'msg' => '密码不正确',
                'name' => 'login-pwd-error',
            ];
        }
    }

    public function editPassword(){
        $user = [];
        $phone = $_POST['phone'];
        if(empty($phone)){
            return [
                'data' => FAIL,
                'msg' => '手机号不能为空',
                'name' => 'chpwd-phone-error',
            ];
        }
        if(!checkPhone($phone)){
            return [
                'data' => FAIL,
                'msg' => '手机号格式不正确',
                'name' => 'chpwd-phone-error',
            ];
        }
        $code = $_POST['code'];
        $redis = new Redis();
        $key = $phone . 'chpwd';
        if($redis->get($key) != $code){
            return [
                'data' => FAIL,
                'msg' => '验证码不正确',
                'name' => 'chpwd-code-error',
            ];
        }
        $user['password'] = $_POST['pwd'];
        if(empty($user['password'])){
            return [
                'data' => FAIL,
                'msg' => '密码不能为空',
                'name' => 'chpwd-pwd-error',
            ];
        }
        if(!checkPassword($user['password'])){
            return [
                'data' => FAIL,
                'msg' => '密码不符合要求',
                'name' => 'chpwd-pwd-error',
            ];
        }
        $repwd = $_POST['repwd'];
        if(empty($repwd)){
            return [
                'data' => FAIL,
                'msg' => '确认密码不能为空',
                'name' => 'chpwd-repwd-error',
            ];
        }
        if($user['password'] != $repwd){
            return [
                'data' => FAIL,
                'msg' => '两次输入的密码不一致',
                'name' => 'chpwd-repwd-error',
            ];
        }
        $uuid = Db::table('nr_user')->where(['phone'=>$phone])->select();
        if(!$uuid){
            return [
                'data' => FAIL,
                'msg' => '不存在此用户',
                'name' => 'chpwd-phone-error',
            ];
        }
        $user['update_time'] = time();
        $user['password'] = md5($user['password']);
        $ret = Db::table('nr_user')->where(['phone'=>$phone])->update($user);
        if($ret){
            return [
                'data' => SUCCESS,
                'msg' => '修改成功',
            ];
        }else{
            return [
                'data' => FAIL,
                'msg' => '修改失败',
                'name' => '',
            ];
        }
    }
    public function checkLogin(){

        if(cookie('userId') && cookie('userId') != ""){
            session('userId',cookie('userId'));
            error_log('session = ' . print_r($_SESSION,true),3,'../../log');
            return [
                'data' => SUCCESS,
                'msg' => session('userId'),
            ];
        }else{
            return [
                'data' => FAIL,
                'msg' => '未登录',
            ];
        }
    }

    public function logout(){
        if(cookie('userId') && cookie('userId')!=""){
            if(session('userId')){
                session('userId',null);
            }
            if(session('username')){
                session('username',null);
            }
            return [
                'data' => SUCCESS,
                'msg' => '退出成功',
            ];
        }
        else{
            return [
                'data' => FAIL,
                'msg' => '退出失败'
            ];
        }
    }

    public function toSetting(){
        $phone = session('userId');
        $ret = Db::table('nr_user')->field('name,username')->where(['phone'=>$phone])->select();
        $this->assign('user',array_shift($ret));
        $this->loadCity();
        return view('index/setting');
    }

    public function saveSetting(){
        $user = [];
        $user['username'] = $_POST['username'];
        $user['name'] = $_POST['name'];
        if(empty($user['username'])){
            return [
                'data' => FAIL,
                'msg' => '保存失败',
            ];
        }
        $user['update_time'] = time();
        $phone = session('userId');
        $ret = Db::table('nr_user')->where(['phone'=>$phone])->update($user);
        if($ret){
            return [
                'data' => SUCCESS,
                'msg' => '保存成功',
            ];
        }else{
            return [
                'data' => FAIL,
                'msg' => '保存失败',
            ];
        }
    }
    public function saveNewPwd(){
        $phone = session('userId');
        $pwd = $_POST['pwd'];
        $pwd2 = $_POST['pwd2'];
        $pwd3 = $_POST['pwd3'];
        if(!checkPassword($pwd2)){
            return [
                'data' => FAIL,
                'msg' => '密码必须为6-1位的数字和字母组成',
            ];
        }
        if($pwd2 != $pwd3){
            return [
                'data' => FAIL,
                'msg' => '两次输入的密码不一致',
            ];
        }
        $user = [];
        $user['phone'] = $phone;
        $user['password'] = md5($pwd);
        $ret = Db::table('nr_user')->where($user)->select();
        if(!$ret){
            return [
                'data' => FAIL,
                'msg' => '原密码不正确',
            ];
        }
        unset($user['phone']);
        $user['password'] = md5($pwd2);
        $user['update_time'] = time();
        $ret = Db::table('nr_user')->where(['phone'=>$phone])->update($user);
        if($ret){
            return [
                'data' => SUCCESS,
                'msg' => '修改成功',
            ];
        }else{
            return [
                'data' => FAIL,
                'msg' => '修改失败',
            ];
        }

    }

    public function score(){
        $userId = session('userId');
        $ret = Db::table('nr_user')->where(['phone'=>$userId])->field('score')->select();
        $this->assign('user',array_shift($ret));
        $this->loadCity();
        return view('index/score');
    }
    public function address(){
        $userId = session('userId');
        $ret = Db::table('nr_userinfo')
            ->field('id,name,user_id,phone,address,address_detail,status,city_code')
            ->where(['user_id'=>$userId,'is_delete'=>0])
            ->order('status desc,create_time desc')
            ->select();
        foreach ($ret as &$item){
            $item['address'] = $item['address'] . $item['address_detail'];
        }
        unset($item);
        $this->assign('userinfo',$ret);
        $this->loadCity();
        return view('index/address');
    }
    public function toOrder(){

        return view('index/order');
    }

    public function balance(){
        $userId = session('userId');
        $ret = Db::table('nr_user')->where(['phone'=>$userId])->field('balance')->select();
        $this->assign('user',array_shift($ret));
        $this->loadCity();
        return view('index/balance');
    }
    public function loadmyinfo(){
        $userId = $_POST['userId'];
        $user = Db::table('nr_user')->field('phone,username,name,balance,score')->where(['phone'=>$userId])->select();

        $address = Db::table('nr_userinfo')
            ->field('phone,name,address,address_detail,city_code')
            ->where(['user_id'=>$userId,'is_delete'=>0])
            ->order('status desc,create_time desc')
            ->select();
        $ret = $user[0];
        $address = array_shift($address);
        $ret['recPhone'] = $address['phone'];
        $ret['recName'] = $address['name'];
        $ret['recAddress'] = $address['address'] . $address['address_detail'];
        $ret['recCityCode'] = $address['city_code'];
        if($ret){
            return [
              'data'=>SUCCESS,
              'info'=>$ret,
            ];
        }else{
            return [
                'data'=>FAIL,
                'msg'=>'数据库出错',
            ];
        }
    }
}