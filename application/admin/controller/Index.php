<?php
/**
 * Created by PhpStorm.
 * User: xkq72
 * Date: 2018/9/3
 * Time: 20:36
 */

namespace app\admin\controller;

class Index extends Base
{
    public function index(){
        return 'welcome!';
    }
    public function uploadFile(){
        if(IS_POST){
            $file=$this->request->file('file');
            if(empty($file)){
                return '没有获取到上传文件。';
            }
            $info=$file->validate([
                'size'=>1048576,
                'ext'=>'jpg,png,gif'
            ])->rule('md5')->move('../uploads');
            //这里采用MD5来给文件重命名，前两个字符是文件夹名称。
            if($info){
                echo $info->getSaveName();
            }else{
                echo $file->getError();
            }
            return'';
        }else{
            return $this->fetch();
        }
    }
    public function login(){

        return $this->fetch();

    }
    public function loginOut(){
        return "退出成功";
    }

}