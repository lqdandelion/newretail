<?php
/**
 * Created by PhpStorm.
 * User: lvqian
 * Date: 2018/4/14
 * Time: 17:52
 */

namespace app\admin\controller;
use think\Controller;
use think\Db;


class Config extends Base {
    const FAIL = 1;
    const SUCCESS = 0;
    public function test(){
        $this->assign('webtitle',config('web_title'));
        return view('public/test5');
    }


    public function doRegister(){
        $this->clearSession();
        $username =	trim($_POST['username']);
        $phone = trim($_POST['phone']);
        $password =	trim($_POST['password']);
        $repassword = trim($_POST['repassword']);
        $result = [];
        if(!$username){
            $result['msg'] = "用户名不能为空";
            $result['data'] = self::FAIL;
            return $result;
        }
        if(!$phone) {
            $result['msg'] = "手机号不可以为空";
            $result['data'] = self::FAIL;
            return $result;
        }
        if(!$password) {
            $result['msg'] = "密码不可以为空";
            $result['data'] = self::FAIL;
            return $result;
        }
        if(!$repassword) {
            $result['msg'] = "确认密码不可以为空";
            $result['data'] = self::FAIL;
            return $result;
        }
        if(!checkPhone($phone)) {
            $result['msg'] = "手机号不合法";
            $result['data'] = self::FAIL;
            return $result;
        }
        if(!checkPassword($password)) {
            $result['msg'] = "密码不合法";
            $result['data'] = self::FAIL;
            return $result;
        }
        if($password !== $repassword) {
            $result['msg'] = "两次密码不一致";
            $result['data'] = self::FAIL;
            return $result;
        }
        $maps['phone']=$phone;
        $uuid = Db::table('nr_business_user')->field('id,username')->where($maps)->select();
        if($uuid){
            $result['msg']="该手机号已注册";
            $result['data'] = self::FAIL;
            return $result;
        }
        $data = [
            'username' => $username,
            'phone' => $phone,
            'password' =>md5($password),
            'create_time' => time(),
            'update_time' => time(),
            'last_login_time' => time(),
        ];
        $uuid = Db::table('nr_business_user')->insertGetId($data);
        if($uuid){
            session('b_id',$uuid);
            session('b_username',$username);
            session('b_phone',$phone);
            return [
                'msg' => json_encode($_SESSION),
                'data' => self::SUCCESS,
            ];
        }else{
            return [
                'msg' => "注册失败",
                'data' => self::FAIL,
            ];
        }

        //error_log('post = ' . $_POST['username '],3,'../../log');


    }

    public function toLogin(){
        $this->assign('webtitle',config('web_title'));
        return view('public/login');

    }

    public function toRegister(){
        $this->assign('webtitle',config('web_title'));
        return view('public/register');
    }



    public function doLogin(){
        $this->clearSession();
        $this->assign('webtitle',config('web_title'));

        $phone =	trim($_POST['phone']);
        $password =	trim($_POST['password']);
        $verify =	trim($_POST['verify']);

        if(!$phone) {
            $result['msg'] = "手机号不可以为空";
            $result['data'] = self::FAIL;
            return $result;
        }
        if(!$password) {
            $result['msg'] = "密码不可以为空";
            $result['data'] = self::FAIL;
            return $result;
        }
        if(!$verify) {
            $result['msg'] = "验证码不可以为空";
            $result['data'] = self::FAIL;
            return $result;
        }
        if(!captcha_check($verify)){
            $result['msg'] = "验证码不正确";
            $result['data'] = self::FAIL;
            return $result;
        }
        $maps['phone']=$phone;
        $maps['password'] = md5($password);
        $uuid = Db::table('nr_business_user')->field('id,username')->where($maps)->select();

        if(!$uuid){
            $result['msg'] = "用户不存在或密码错误";
            $result['data'] = self::FAIL;
            return $result;
        } else{
            $uuid = array_shift($uuid);
            $data = [
                'last_login_time' => time(),
            ];
            $ret = Db::table('nr_business_user')->where('id=' . $uuid['id'])->update($data);
            if(!$ret){
                $result['msg'] = "登录失败";
                $result['data'] = self::FAIL;
            }else{
                session('b_id',$uuid['id']);
                session('b_username',$uuid['username']);
                session('b_phone',$phone);

                $ret = Db::table('nr_shopinfo')->where(['bus_user_id'=>$uuid['id']])->field('shop_id')->select();
                if($ret){
                    session('shop_id',$ret[0]['shop_id']);
                    $result['hasPerfect'] = true;
                }else{
                    $result['hasPerfect'] = false;
                }
                $result['msg'] = json_encode($_SESSION);
                $result['data'] = self::SUCCESS;
            }
            return $result;
        }

    }

    /**
     * 用户退出
     */
    public function logout(){
        session('b_id',null);
        session(null);
        //dump($_SESSION);
        session_destroy();
        $this->assign('webtitle',config('web_title'));
        return view('public/login');
    }

}
