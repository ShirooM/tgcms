<?php
/**
 * Created by PhpStorm.
 * User: xkq72
 * Date: 2018/6/2
 * Time: 22:47
 */

namespace app\index\controller;

use think\Controller;
use think\Db;

class Base extends Controller{
    public $source=[];

    public function initialize()
    {
        define('UPLOADS_PATH','/uploads');
        try{
            $this->source=Db::name('source')->select();
        }catch (\Exception $exception){
            $this->source=array();
        }

    }
    public function errorPage($title='',$content=''){
        return $this->fetch('public/error',['title'=>$title,'content'=>$content]);
    }
}