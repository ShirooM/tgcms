<?php
/**
 * Created by PhpStorm.
 * User: xkq72
 * Date: 2018/9/4
 * Time: 10:16
 */

namespace app\admin\controller;


use think\Db;

class Page extends Base
{
    public function index(){

    }
    public function add(){
        if(IS_POST){
            $postData=$this->request->param();

            $result=$this->validate($postData,'app\admin\validate\Page.add');
            if($result!==true){
                //如果检检验不过关，提示错误。
                $this->error($result);
                return false;
            }

            $alias=$postData['alias'];
            $modelID=$postData['model_id'];
            $domainID=$postData['domain_id'];
            $userID=$postData['user_id'];

            $data=[
                'page_alias'=>$alias,
                'page_model_id'=>$modelID,
                'page_domain_id'=>$domainID,
                'page_user_id'=>$userID
            ];
            //这里先查询要新增的落地页是否已经存在。
            try{
                $result=Db::name('page')->where(['page_alias'=>$alias,'page_domain_id'=>$domainID])->find();
            }catch (\Exception $e){
                $this->error('新增落地页异常，请重试。0');
                return false;
            }
            if(!is_null($result)){
                $this->error('新增的落地页已经存在。');
                return false;
            }
            try{
                $result=Db::name('page')->insert($data);
            }catch (\Exception $e){
                $this->error('新增落地页异常，请重试。1');
                return false;
            }
            if($result){
                //成功
                $this->success('添加成功。');
                return true;
            }else{
                //失败
                $this->error('添加失败。');
                return false;
            }
        }else{
            try{
                $domain=Db::name('domain')->field('domain_id,domain_url')->select();
                $model=Db::name('model')->field('model_id,model_name,model_pc_filename')->select();
                $user=Db::name('user')->field('user_id,user_name,user_weixin')->select();
            }catch (\Exception $e){
                return '读取数据出现异常，请重新刷新页面。';
            }
            $this->assign('domain',$domain);
            $this->assign('model',$model);
            $this->assign('user',$user);
            return $this->fetch();
        }
    }
    public function batchAdd(){

    }
    public function delete(){
        $postData=$this->request->param();
        //检验数据合法性
        $result=$this->validate($postData,'app\admin\validate\Page.delete');
        if($result!==true){
            //如果检检验不过关，提示错误。
            $this->error($result);
            return false;
        }
        $pageID=$postData['page_id'];
        try{
            $result=Db::name('page')->where('page_id',$pageID)->delete();
        }catch (\Exception $e){
            $this->error('删除异常，请重试。');
            return false;
        }
        if($result){
            $this->success('删除成功。');
            return true;
        }else{
            $this->success('删除失败。');
            return false;
        }
    }
    public function edit(){
        if(IS_POST){
            $postData=$this->request->param();
            $result=$this->validate($postData,'app\admin\validate\Page.edit_post');
            if($result!==true){
                //如果检检验不过关，提示错误。
                $this->error($result);
                return false;
            }
            $pageID=$postData['page_id'];
            $alias=$postData['alias'];
            $modelID=$postData['model_id'];
            $domainID=$postData['domain_id'];
            $userID=$postData['user_id'];
            try{
                $result=Db::name('page')->where('page_id',$pageID)
                    ->update([
                        'page_alias'=>$alias,
                        'page_model_id'=>$modelID,
                        'page_domain_id'=>$domainID,
                        'page_user_id'=>$userID
                    ]);
            }catch (\Exception $e){
                $this->error('修改数据出现异常，请重试。');
                return false;
            }
            if($result){
                $this->success('修改成功。','admin/page/show');
                return true;
            }else{
                $this->error('修改失败。');
                return false;
            }
        }else{
            $postData=$this->request->param();
            //检验数据合法性
            $result=$this->validate($postData,'app\admin\validate\Page.edit');
            if($result!==true){
                //如果检检验不过关，提示错误。
                $this->error($result);
                return false;
            }
            $pageID=$postData['page_id'];
            try{
                $result=Db::name('page')->where('page_id',$pageID)->find();
            }catch (\Exception $e){
                $this->error('读取数据异常，请重试。');
                return false;
            }

            //查询其它选项列表
            try{
                $domain=Db::name('domain')->field('domain_id,domain_url')->select();
                $model=Db::name('model')->field('model_id,model_name,model_pc_filename')->select();
                $user=Db::name('user')->field('user_id,user_name,user_weixin')->select();
            }catch (\Exception $e){
                return '读取数据出现异常，请重新刷新页面。';
            }
            $this->assign('domain',$domain);
            $this->assign('model',$model);
            $this->assign('user',$user);


            $this->assign('result',$result);
            return $this->fetch();
        }
    }
    public function show(){
        $postData=$this->request->param();
        //检验数据有效性
        $domain_id=$postData['domain_id'];

        if(IS_POST){
            try{
                $result=Db::name('page')
                    ->join('domain','page_domain_id=domain_id')
                    ->join('model','page_model_id=model_id')
                    ->join('model_dir','model_dir_id=m_model_dir_id')
                    ->join('brand','page_brand_id=brand_id')
                    ->where('page_domain_id',$domain_id)
                    ->select();
            }catch (\Exception $e){
                return json_shiroo('database');
            }
            return json_shiroo(0,'',count($result),$result);
        }else{
            try{
                $rand=Db::name('brand')->select();
                $domain=Db::name('domain')->select();
            }catch (\Exception $e){
                $this->error(err('database')['msg']);
            }
            $this->assign('brand',$rand);
            $this->assign('domain',$domain);
            $this->assign('domain_id',$domain_id);
            return $this->fetch();
        }
    }
}