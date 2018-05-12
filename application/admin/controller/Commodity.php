<?php
/**
 * Created by PhpStorm.
 * User: lvqian
 * Date: 2018/4/25
 * Time: 14:20
 */
namespace app\admin\controller;
use app\admin\controller\Base;
use Prophecy\Comparator\Factory;
use think\Controller;
use think\Loader;
use think\Request;
use think\Db;
use think\Response;
use ORG\Util\Page;
class Commodity extends Base {
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }
    public function toAddComm(){
        $shop_id = session('shop_id');

        $where['shop_id'] = $shop_id;
        $where['is_delete'] = 0;
        $cateList = Db::table('nr_category')
            ->where($where)
            ->field('id,cate_id,cate_name')
            ->order('create_time desc')
            ->select();
        $this->assign('cateList',$cateList);
        if(empty($cateList)){
            $this->error('还没有分类,请去添加分类');
        }
        return view('index/addComm');
    }
    public function addComm(){
        $comm = [];
        $comm['cate_id'] = $_POST['comm_cate'];
        $comm['comm_name'] = $_POST['comm_name'];
        $comm['comm_price'] = $_POST['comm_price'];

        if(!$comm['comm_name']){
            return [
                'data' => FAIL,
                'msg' => '商品名不能为空',
            ];
        }
        $comm['comm_id'] = time() + rand(1000,100000);
        $uploadFile = picupload($comm['comm_id']);
        if($uploadFile['data'] == FAIL){
            return $uploadFile;
        }
        $comm['comm_pic'] = $uploadFile['msg'];
        if(!$comm['comm_price']){
            return [
                'data' => FAIL,
                'msg' => '商品价格不能为空',
            ];
        }
        if(!is_numeric($comm['comm_price']) || floatval($comm['comm_price'])<0){
            return [
                'data' => FAIL,
                'msg' => '商品价格必须为不少于0的数字',
            ];
        }

        $comm['create_time'] = $comm['update_time'] = time();
        $ret = Db::table('nr_commodity')->insertGetId($comm);
        if($ret){
            return [
                'data' => SUCCESS,
                'msg' => '添加商品成功',
            ];
        }else{
            return [
                'data' => FAIL,
                'msg' => '添加商品失败',
            ];
        }
    }
    public function toEditComm($id){
        $ret = Db::table('nr_commodity')
            ->field('id,comm_id,cate_id,comm_pic,comm_price,comm_name')
            ->where(['id'=>$id])
            ->select();
        $ret = array_shift($ret);
        $cate = Db::table('nr_category')
            ->field('cate_name')
            ->where(['cate_id'=>$ret['cate_id']])
            ->select();
        $cate = array_shift($cate);
        $ret['cate_name'] = $cate['cate_name'];
        unset($ret['cate_id']);
        $this->assign('comm',$ret);
        return view('index/editComm');

    }
    public function editComm(){
        error_log('edit is in',3,'../../log');
        $comm = [];
        $id = $_POST['id'];
        $comm['comm_name'] = $_POST['comm_name'];
        $comm['comm_price'] = $_POST['comm_price'];

        if(!$comm['comm_name']){
            return [
                'data' => FAIL,
                'msg' => '商品名不能为空',
            ];
        }
        $comm['comm_id'] = $_POST['comm_id'];
        $uploadFile = picupload($comm['comm_id']);
        if($uploadFile['data'] == FAIL && empty($_POST['img'])){
            return $uploadFile;
        }
        if($uploadFile['data'] == SUCCESS) {
            $comm['comm_pic'] = $uploadFile['msg'];
        }
        if(!$comm['comm_price']){
            return [
                'data' => FAIL,
                'msg' => '商品价格不能为空',
            ];
        }
        if(!is_numeric($comm['comm_price']) || floatval($comm['comm_price'])<0){
            return [
                'data' => FAIL,
                'msg' => '商品价格必须为不少于0的数字',
            ];
        }

        $comm['update_time'] = time();
        $ret = Db::table('nr_commodity')->where(['id'=>$id])->update($comm);
        if($ret){
            return [
                'data' => SUCCESS,
                'msg' => '修改商品成功',
            ];
        }else{
            return [
                'data' => FAIL,
                'msg' => '修改商品失败',
            ];
        }
    }

    public function delComm($id){
        $data = [
          'is_delete' => 1,
          'update_time' => time(),
        ];
        $ret = Db::table('nr_commodity')->where(['id'=>$id])->update($data);
        if($ret){
            $this->redirect('commodity/listcomm');
        }else{
            $this->error('删除失败');
        }
    }

    public function upComm($id){
        $data = [
            'status' => 0,
            'update_time' => time(),
        ];
        $ret = Db::table('nr_commodity')->where(['id'=>$id])->update($data);
        if($ret){
            $this->redirect('commodity/listcomm');
        }else{
            $this->error('上架失败');
        }
    }

    public function downComm($id){
        $data = [
            'status' => 1,
            'update_time' => time(),
        ];
        $ret = Db::table('nr_commodity')->where(['id'=>$id])->update($data);
        if($ret){
            $this->redirect('commodity/listcomm');
        }else{
            $this->error('下架失败');
        }
    }

    public function listComm(){
        $comm_name = null;
        $cate_name = null;
        if(isset($_GET['comm_'])) {
            $comm_name = $_GET['comm_'];
            $comm_name = trim($comm_name);
        }
        if(isset($_GET['cate_'])) {
            $cate_name = $_GET['cate_'];
            $cate_name = trim($cate_name);
        }
        if(!empty($cate_name)) {
            $conds['cate_name'] = ['like', '%' . $cate_name . '%'];
        }
        $conds['shop_id'] = session('shop_id');
        $conds['is_delete'] = 0;
        $cate = Db::table('nr_category')->where($conds)->field('cate_id,cate_name')->select();
        $cateIds = array_column($cate,'cate_id');
        $cate = array_combine($cateIds,array_column($cate,'cate_name'));
        if(!empty($comm_name)){
            $data['comm_name'] = ['like','%' . $comm_name . '%'];
        }
        $data['cate_id'] = ['in',$cateIds];
        $data['is_delete'] = 0;
        $fields = "id,comm_id,comm_name,cate_id,comm_pic,comm_price,status,create_time";
        $list = Db::table('nr_commodity')
            ->where($data)
            ->field($fields)
            ->order('create_time desc')
            ->paginate(10,false,['query' => request()->param()])
            ->each(function ($item) use($cate) {
                $item['cate_name'] = $cate[$item['cate_id']];
                $item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
                return $item;
            });

        // 获取分页显示
        $page = $list->render();
        // 模板变量赋值
        $this->assign('comm_name',$comm_name);
        $this->assign('cate_name',$cate_name);
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return view('index/listComm');
    }
}