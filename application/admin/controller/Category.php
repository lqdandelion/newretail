<?php
/**
 * Created by PhpStorm.
 * User: lvqian
 * Date: 2018/4/24
 * Time: 16:38
 */

namespace app\admin\controller;
use app\admin\controller\Base;
use think\Controller;
use think\Request;
use think\Db;

class Category extends Base {
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }
    public function toAddCate(){
        return view('index/addCate');
    }
    public function addCate(){
        $cate = [];
        $cate['cate_name'] = $_POST['cname'];
        $cate['cate_weight'] = $_POST['weight'];
        if(!$cate['cate_name']){
            return [
                'data' => FAIL,
                'msg' => '分类名不能为空',
            ];
        }
        if(!$cate['cate_weight']){
            return [
                'data' => FAIL,
                'msg' => '权重不能为空',
            ];
        }
        if(!is_numeric($cate['cate_weight']) || !is_int($cate['cate_weight']+0)){
            return [
                'data' => FAIL,
                'msg' => '权重必须为整数',
            ];
        }
        $cate['cate_weight'] = intval($cate['cate_weight']);
        if($cate['cate_weight']<0){
            return [
                'data' => FAIL,
                'msg' => '权重必须为非负数',
            ];
        }
        $cate['cate_id'] = time()+rand(10,10000);
        $cate['shop_id'] = session('shop_id');
        $cate['create_time'] = $cate['update_time'] = time();
        $ret = Db::table('nr_category')->insertGetId($cate);
        if($ret){
            return [
                'data' => SUCCESS,
                'msg' => json_encode($ret),
            ];
        }else{
            return [
                'data' => FAIL,
                'msg' => '添加失败',
            ];
        }
    }

    public function delCate($id){
        $data = [
            'is_delete' => 1,
            'update_time' => time(),
        ];
        $ret =  Db::table('nr_category')->where(['id'=>$id])->update($data);
        if($ret){
            $this->redirect('category/listcate');
        }
    }
    public function toEditCate(){
        //error_log('post = ' . print_r($_POST,true),3,'../../log');
        $id = Request::instance()->param('id');
        $cate = DB::table('nr_category')->where(['id'=>$id])->field('id,cate_id,cate_name,cate_weight')->select();
        $this->assign('cate',array_shift($cate));
        return view('index/editCate');
    }

    public function editCate(){
        $cate = [];
        $id = $_POST['id'];
        $cate['cate_name'] = $_POST['cname'];
        $cate['cate_weight'] = $_POST['weight'];
        if(!$cate['cate_name']){
            return [
                'data' => FAIL,
                'msg' => '分类名不能为空',
            ];
        }
        if(!$cate['cate_weight']){
            return [
                'data' => FAIL,
                'msg' => '权重不能为空',
            ];
        }
        if(!is_numeric($cate['cate_weight']) || !is_int($cate['cate_weight']+0)){
            return [
                'data' => FAIL,
                'msg' => '权重必须为整数',
            ];
        }
        $cate['cate_weight'] = intval($cate['cate_weight']);
        if($cate['cate_weight']<0){
            return [
                'data' => FAIL,
                'msg' => '权重必须为非负数',
            ];
        }
        $cate['update_time'] = time();

        $ret = Db::table('nr_category')->where(['id'=>$id])->update($cate);
        if($ret){
            return [
                'data' => SUCCESS,
                'msg' => json_encode($ret),
            ];
        }else{
            return [
                'data' => FAIL,
                'msg' => '修改失败',
            ];
        }
    }

    public function listCate(){
        $shop_id = session('shop_id');
        $cname = Request::instance()->param('cname');
        if(!empty($cname)){
            $where['cate_name'] = ['like', '%'.$cname.'%'];
        }
        error_log('cname = ' . $cname,3,'../../log');
        $where['shop_id'] = $shop_id;
        $where['is_delete'] = 0;
        $cateList = Db::table('nr_category')
            ->where($where)
            ->field('id,cate_id,cate_name,cate_weight,create_time')
            ->order('cate_weight desc,create_time desc')
            ->select();
        foreach ($cateList as &$cate){
            $cate['cid'] = $cate['cate_id'];
            $cate['cname'] = $cate['cate_name'];
            $cate['weight'] = $cate['cate_weight'];
            $cate['create_time'] = date('Y-m-d H:m:i',$cate['create_time']);
            unset($cate['cate_id']);
            unset($cate['cate_name']);
            unset($cate['cate_weight']);
        }
        unset($cate);
        $this->assign('cname',$cname);
        $this->assign('cateList',$cateList);
        return view('index/listCate');
    }




}