<?php
/**
 * Created by PhpStorm.
 * User: lvqian
 * Date: 2018/5/11
 * Time: 13:33
 */

namespace app\index\controller;
use think\Db;
use think\Controller;

class Order extends Base{
    public  function confirm(){
        $this->loadCity();
        return view('shop/order_confirm');
    }
    public function getPayPrice(){
        $userId = $_POST['userId'];
        $shopId = $_POST['shopId'];
        $itemsTxt = $_POST['itemsTxt'];
        error_log('payPrice = ' . print_r($_POST,true),3,'../../log');

        $payPrice = $this->getOriginalPrice($userId,$shopId,$itemsTxt);
        $shopinfo = Db::table('nr_shopinfo')->field('send_price,delivery')->where(['shop_id'=>$shopId])->select();
        $shopinfo = array_shift($shopinfo);
        if($payPrice<$shopinfo['send_price']){
            return [
                'data'=>FAIL,
                'msg'=>'不到起送价，不予配送',
            ];
        }
        $payPrice += $shopinfo['delivery'];
        error_log('payPrice = ' . $payPrice,3,'../../log');
        $disCount=0;
        $score = Db::table('nr_user')->field('score')->where(['phone'=>$userId])->select();
        $score = $score[0]['score'];
        $disCount = 0;
        if($score&&$score>0){
            $disCount=floor($score/100);//取整
        }

        //减去积分优惠
        $payPrice-=$disCount;
        if($payPrice<=0){
            $payPrice=0;
        }
        return [
            'data'=>0,
            'payPrice'=>$payPrice,
        ];
    }
    public function getOriginalPrice($userId,$shopId,$itemsTxt){
        $payPrice=0;
        if($userId&&$shopId&&$itemsTxt){
            $arrayObj=json_decode($itemsTxt,true);
            error_log('arr='.print_r($arrayObj,true),3,'../../log');
            $itemIds = [];
            foreach ($arrayObj as $item){
                array_push($itemIds,$item['itemId']);
            }
            error_log('ids = ' . print_r($itemIds,true),3,'../../log');
            $where['comm_id'] = ['in',$itemIds];
            $comm = Db::table('nr_commodity')
                ->field('comm_price,comm_id')
                ->where($where)
                ->select();
            error_log('comm = ' . print_r($comm,true),3,'../../log');
            $commId = array_column($comm,'comm_id');
            $comm = array_combine($commId,array_column($comm,'comm_price'));
           foreach ($arrayObj as $item){
                $itemId= $item['itemId'];
                $count = $item['count'];
                $perPrice=$comm[$itemId];
                $payPrice=$payPrice+$perPrice*$count;
            }
        }
        return $payPrice;
    }

    public function commitOrder(){
        $order = [
            'user_id' => $_POST['userId'],
            'user_name' => $_POST['username'],
            'shop_id' => $_POST['shopId'],
            'shop_name' => $_POST['shopName'],
            'shop_phone' => $_POST['shopPhone'],
            'oprice' => $_POST['price'],
            'rec_name' => $_POST['recName'],
            'rec_phone' => $_POST['recPhone'],
            'rec_address' => $_POST['recAddress'],
            'pay_method' => $_POST['paymethod'],
            'mark' => $_POST['orderRemark'],
            'create_time' => time(),
            'update_time' => time(),
        ];
        $order['oid'] = time() . rand(10000,99999);
        Db::startTrans();
        try{
            error_log('order = ' . print_r($order,true),3,'../../log');
            $id = Db::table('nr_order')->insertGetId($order);

            $items = json_decode($_POST['items'],true);
            $data = [];
            foreach ($items as $item){
                $data[] = [
                    'order_id' => $id,
                    'comm_id' => $item['itemId'],
                    'comm_name' => $item['name'],
                    'comm_count' => $item['count'],
                    'comm_price' => $item['price'],
                    'create_time' => time(),
                    'update_time' => time(),
                ];
            }
            $ret = Db::table('nr_order_detail')->insertAll($data);
            Db::commit();
            return [
                'data' => SUCCESS,
                'msg' => '下单成功',
            ];
        } catch (\Exception $e) {
            error_log('msg = ' . print_r($e,true),3,'../../log');
            // 回滚事务
            Db::rollback();
            return [
                'data' => FAIL,
                'msg' => '下单失败',
            ];
        }
    }
    public function orderList(){
        $this->loadCity();
        $userId = session('userId');
        $list = Db::table('nr_order')
            ->field('id,oid,oprice,rec_phone,rec_name,rec_address,shop_id,shop_name,shop_phone,create_time,pay_method,mark,create_time')
            ->where(['user_id'=>$userId])
            ->order('create_time desc')
            ->paginate(5,false,['query' => request()->param()]);
        $page = $list->render();
        $list = $list->all();
        $orderIds = array_column($list,'id');
        error_log('orderIds = ' . print_r($orderIds,true),3,'../../log');
        $where['order_id'] = ['in',$orderIds];


        $orderDetail = Db::table('nr_order_detail')
            ->field('order_id,comm_id,comm_name,comm_count,comm_price')
            ->where($where)
            ->select();
        error_log('orderDetail = ' . print_r($orderDetail,true),3,'../../log');

        $orderDetails = [];
        foreach ($orderDetail as $detail){
            $tmp = [
                'name'=>$detail['comm_name'],
                'count'=>$detail['comm_count'],
                'price'=>$detail['comm_price'],
            ];
            $orderDetails[$detail['order_id']][] = $tmp;
        }
        error_log('orderDetails = ' . print_r($orderDetails,true),3,'../../log');
        $rows = [];
        foreach ($list as &$item){
            $item['paymethod'] = $item['pay_method'];
            unset($item['pay_method']);
            $item['price'] = $item['oprice'];
            unset($item['oprice']);
            $item['shopId'] = $item['shop_id'];
            unset($item['shop_id']);
            $item['shopName'] = $item['shop_name'];
            unset($item['shop_name']);
            $item['shopPhone'] = $item['shop_phone'];
            unset($item['shop_phone']);
            $item['items'] = $orderDetails[$item['id']];
            $item['orderId'] = $item['oid'];
            $item['orderAddress'] = $item['rec_address'];
            unset($item['oid']);
            unset($item['rec_address']);
            $item['orderName'] = $item['rec_name'];
            unset($item['rec_name']);
            $item['orderPhone'] = $item['rec_phone'];
            unset($item['rec_phone']);
            $item['orderRemark'] = $item['mark'];
            unset($item['mark']);
            $item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
            $rows[] = [
                'username'=>'lvqian',
                'received' => 3,
                'paymethod'=> $item['pay_method'],
                'price' => $item['oprice'],
                'shopId' => $item['shop_id'],
                'shopName' => $item['shop_name'],
                'shopPhone' => $item['shop_phone'],
                'items' => $orderDetails[$item['id']],
                'orderId' => $item['oid'],
                'orderAddress' => $item['rec_address'],
                'orderName' => $item['rec_name'],
                'orderPhone' => $item['rec_phone'],
                'orderRemark' => $item['mark'],
            ];
        }
        error_log('rows = ' . print_r($rows,true),3,'../../log');

        $this->assign('rows',$rows);
        return view('index/order');
    }

}