<?php
/**
 * Created by PhpStorm.
 * User: lvqian
 * Date: 2018/5/8
 * Time: 20:41
 */
namespace app\index\controller;

use think\Controller;
use think\Db;

class Address extends Controller {
    public function add(){
        return view('address/add');
    }
    public function save(){
        $userId = session('userId');
        $num = Db::table('nr_userinfo')->where(['user_id'=>$userId,'is_delete'=>0])->count();
        if($num >=10 ){
            return [
                'data' => FAIL,
                'msg' => '地址数量已达上线',
            ];
        }
        $phone = $_POST['phone'];
        $name = $_POST['name'];
        $cityCode = $_POST['cityCode'];
        $address = $_POST['address'];
        $detail = $_POST['address_detail'];
        $status = 0;
        if($_POST['status']){
            $status = 1;
        }
        if(!checkPhone($phone)){
            return [
                'data' => FAIL,
                'msg' => '手机号无效',
            ];
        }
        if(empty($name)){
            return [
                'data' => FAIL,
                'msg' => '收货人姓名不能为空',
            ];
        }
        if(empty($address) || empty($detail)){
            return [
                'data' => FAIL,
                'msg' => '收货人地址不全',
            ];
        }
        $userinfo = [
            'user_id' => $userId,
            'phone' => $phone,
            'name' => $name,
            'address' => $address,
            'address_detail' => $detail,
            'city_code' => $cityCode,
            'status' => $status,
            'create_time' => time(),
            'update_time' => time(),
        ];
        Db::startTrans();
        try{
            $ret = Db::table('nr_userinfo')->where(['status'=>1,'user_id'=>$userId])->update(['status'=>0,'update_time'=>time()]);
            $id = Db::table('nr_userinfo')->insertGetId($userinfo);
            if ($id) {
                Db::commit();
                return [
                    'data' => SUCCESS,
                    'msg' => '保存成功',
                ];
            } else {
                return [
                    'data' => FAIL,
                    'msg' => '保存失败',
                ];
            }
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
                return [
                    'data' => FAIL,
                    'msg' => '保存失败',
                ];
        }

    }
    public function edit(){
        error_log('POST = ' . print_r($_POST,true),3,'../../log');
        $id = $_POST['id'];
        $userId = session('userId');
        $phone = $_POST['phone'];
        $name = $_POST['name'];
        $cityCode = $_POST['cityCode'];
        $address = $_POST['address'];
        $detail = $_POST['address_detail'];
        $status = 0;
        if($_POST['status'] === true){
            $status = 1;
        }
        if(!checkPhone($phone)){
            return [
                'data' => FAIL,
                'msg' => '手机号无效',
            ];
        }
        if(empty($name)){
            return [
                'data' => FAIL,
                'msg' => '收货人姓名不能为空',
            ];
        }
        if(empty($address) || empty($detail)){
            return [
                'data' => FAIL,
                'msg' => '收货人地址不全',
            ];
        }
        $userinfo = [
            'phone' => $phone,
            'name' => $name,
            'address' => $address,
            'address_detail' => $detail,
            'city_code' => $cityCode,
            'status' => $status,
            'update_time' => time(),
        ];
        if($status == 1){
            Db::startTrans();
            try{
                Db::table('nr_userinfo')->where(['status'=>1,'user_id'=>$userId])->update(['status'=>0,'update_time'=>time()]);
                Db::table('nr_userinfo')->where(['id'=>$id])->update($userinfo);
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return [
                    'data' => SUCCESS,
                    'msg' => '修改成功',
                ];
            }
            return [
                'data' => FAIL,
                'msg' => '修改失败',
            ];
        }else{
            $ret = Db::table('nr_userinfo')->where(['id'=>$id])->update($userinfo);
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
    }

    public function delete($id){
        $data = [
            'is_delete' => 1,
            'update_time' => time(),
        ];
        $ret = Db::table('nr_userinfo')->where(['id'=>$id])->update($data);
        if($ret){
            $this->redirect('config/address');
        }else{
            $this->error('删除失败');
        }
    }
    public function setDefault($id){
        $userId = session('userId');
        $data = [
            'status' => 1,
            'update_time' => time(),
        ];
        Db::startTrans();
        try{
            $ret = Db::table('nr_userinfo')->where(['status'=>1,'user_id'=>$userId])->update(['status'=>0,'update_time'=>time()]);
            $res = Db::table('nr_userinfo')->where(['id'=>$id])->update($data);
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error(json_decode($e));
        }
        $this->redirect('config/address');
    }
    public function toEdit($id){
        $ret = Db::table('nr_userinfo')->where(['id'=>$id])->select();
        $this->assign('address',array_shift($ret));
        return view('address/edit');
    }
}