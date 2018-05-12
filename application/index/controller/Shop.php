<?php
/**
 * Created by PhpStorm.
 * User: lvqian
 * Date: 2018/5/9
 * Time: 21:31
 */
namespace app\index\controller;

use think\Db;
use think\Controller;

class Shop extends Base{
    public function getShopInfo($id,$cateId=0){
        $shopId = $id;
        $shopInfo = Db::table('nr_shopinfo')
            ->where(['shop_id'=>$shopId])
            ->field('shop_id,shop_name,shop_phone,shop_icon,send_price,delivery,start_time,end_time,address,address_detail')
            ->select();
        $cate = Db::table('nr_category')
            ->field('cate_id,cate_name')
            ->where(['shop_id'=>$shopId,'is_delete'=>0])
            ->order('cate_weight desc,create_time desc')
            ->select();
        if($cateId == 0){
            $cateId = $cate[0]['cate_id'];
        }
        $comm = Db::table('nr_commodity')
            ->field('comm_id,comm_name,comm_pic,comm_price')
            ->where(['cate_id'=>$cateId,'status'=>0,'is_delete'=>0])
            ->select();

        $shopInfo = array_shift($shopInfo);
        $this->assign('actId',$cateId);
        $this->assign('shopInfo',$shopInfo);
        $this->assign('cate',$cate);
        $this->assign('comm',$comm);
        error_log('actId = ' . $cateId,3,'../../log');
        error_log('shopinfo = ' . print_r($shopInfo,true),3,'../../log');
        error_log('cate = ' . print_r($cate,true),3,'../../log');
        error_log('comm = ' . print_r($comm,true),3,'../../log');
        $this->loadCity();
        return view('shop/shopinfo');
    }
    public function getFood(){
        $id = $_GET['cate_id'];
        $food = Db::table('nr_commodity')
            ->field('comm_id,comm_name,comm_pic,comm_price')
            ->where(['cate_id'=>$id])
            ->select();
        if(!$food){
            $food = "";
        }
        return [
            'data'=>SUCCESS,
            'comm'=>$food,
        ];
    }

    public function loadShopInfo(){
        $shopId = $_POST['shopId'];
        $ret = Db::table('nr_shopinfo')
            ->field('shop_id,shop_name,shop_icon,shop_phone,address,send_price,delivery')
            ->where(['shop_id'=>$shopId])
            ->select();
        return [
            'data'=>0,
            'shopinfo'=>array_shift($ret),
        ];
    }

}