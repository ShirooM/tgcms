<?php
/**
 * Created by PhpStorm.
 * User: xkq72
 * Date: 2019/3/21
 * Time: 21:17
 */

namespace app\index\controller;


use think\Db;

class Tongji extends Base
{
    public function add(){
        $postData=$this->request->param();

        //return json_shiroo(0,'aaaa',0,$postData);

        $id=$postData['id'];
        $str=$postData['copy_str'];
        $type=$postData['type'];
        switch ($type){
            case 1:
                //落地页载入完成
                $data=[
                    'tj_load_done'=>1,
                ];
                break;
            case 2:
                //转化。访客复制微信号或点击按钮复制微信号
                $data=[
                    'tj_str'=>$str,
                    'tj_zhuanhua'=>1,
                    'tj_zh_time'=>date('Y-m-d H:i:s')
                ];
                break;
            default:
                $data=[];
        }
        try{
            $tjDB=Db::name('tongji')->where('tj_id',$id)->update($data);
        }catch (\Exception $exception){
            $tjDB=0;
        }
        if($tjDB>0){
            $code=0;
        }else{
            $code=100;
        }
        return json_shiroo($code,'',$tjDB,['type'=>$type,'id'=>$id,'copy_str'=>$str]);
    }
}