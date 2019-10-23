<?php

namespace app\index\controller;

use app\admin\model\Page;
use think\Db;
use think\facade\Cookie;
use app\admin\model\Tongji;
use app\admin\model\Page as PageModel;
use app\admin\model\Domain;

class Index extends Base
{
    public function index()
    {
        //获取访问的落地页别名
        $param = $this->request->param();
        //获取访问的子页面,如果p参数为空的话，默认为index
        $param['p'] = !empty($param['p']) ? $param['p'] : 'index';

        //进行数据的检验,只允许字母与数字组合。
        $validate = $this->validate($param, 'app\index\validate\Index.open');
        if ($validate !== true) {
            //如果检检验不过关，提示错误。
            return $this->errorPage('非法落地页名称');
            //return json_shiroo('validate', $validate);
        }

        //在关键词连接里设置了下面相应的get参数
        $pageName = $param['p'];//获取落地页名
        $source = !empty($param['source']) ? urldecode($param['source']) : '';//渠道
        $plan = !empty($param['plan']) ? urldecode($param['plan']) : '';//计划
        $unit = !empty($param['unit']) ? urldecode($param['unit']) : '';//单元
        $keyword = !empty($param['keyword']) ? urldecode($param['keyword']) : '';//关键词

        $domain_http = $this->request->domain();//获取当前的域名,这里的域名是带http://

        //去掉域名里的http://字符。
        $domain = str_replace('http://', '', $domain_http);
        $domain = str_replace('https://', '', $domain);

        //获取前一页的URL。用于判断是直接输入URL访问还是从搜索引擎点进来的
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        try {
            //首页查询访问落地页的域名是否已在后台绑定
            $domainDB=Domain::where('domain_url', $domain)->findOrEmpty();
            //$domainDB = Db::name('domain')->where()->find();
        } catch (\Exception $e) {
            return $this->errorPage('查询域名数据异常');
        }
        if ($domainDB->isEmpty()) {
            //查询结果没有绑定的话，返回错误提示
            return $this->errorPage('访问的域名没有绑定');
        }

        //从上一个跳转URL的参数里获取搜索词
        $searchKeyword = $this->getSearchKeyword($referer);

        //是否允许访问正常落地页，还是访问审核页
        //根据城市判断
        if(!$this->allowCity($domainDB['domain_restricted_area'])){
            $pageName='ex';
        }
        //根据设置来源
        if (!$this->allowBrowse($referer, $domainDB['domain_source_allow'])) {
            //不允许访问，跳转默认设置页面
            $pageName = 'ex';//如果这里改动了，记得去static/js/tongji.html里也改。
        }

        $where = ['page_domain_id' => $domainDB['domain_id'], 'page_name' => $pageName];
        try {
            //查询落地页，
            $rec = PageModel::where($where)->find();//find查找出来是数组，而select查找出来是数据集对象。
        } catch (\Exception $exception) {
            return $this->errorPage('查询页面数据异常');
            // Log::record('执行查询落地页数据库失败。'.$exception,'error');
        }
        if (is_null($rec) && !is_array($rec)) {
            //访问的落地页不存在的话，返回错误
            return $this->errorPage('页面['.$pageName.']不存在');
        } else {
            //落地页存在
            $pageRec = $rec;
            $domainRec = $rec->domain;//对应model/page里的domain方法
            $templateRec = $rec->template;
            $templateDirRec = $rec->template->templateDir;
            $brandRec = $rec->brand;

            //访客记录(必须能正常访问该页面，才进行统计)
            $tongjiID = $this->tongJi($referer, $source, $keyword, $plan, $unit, $domain, $pageName, $searchKeyword);

            //查询推广线索的扩展参数
            try{
                $defines = Db::name('brand_define_list')
                    ->join('brand_define', 'bd_id=bdl_define_id')
                    ->where('bdl_brand_id', $brandRec->brand_id)->select();
            }catch (\Exception $exception){
                $defines=[];
            }
            $def = [];//定义一个空的数组，用于储存循环读取到的扩展参数。
            if (is_array($defines)) {
                //如果该推广线索存在设置了扩展参数。
                foreach ($defines as $define) {
                    //循环读取设置的所有扩展参数，
                    $def[$define['bd_name']] = $define['bdl_define_var'];
                }
            }

            $sh=[
                'domain_id'=>$domainRec->domain_id,
                'domain_url'=>$domainRec->domain_url,
                'domain_copyright'=>$domainRec->domain_copyright,
                'domain_count_code'=>$domainRec->domain_count_code,
                'page_id'=>$pageRec->page_id,
                'page_name'=>$pageRec->page_name,
                'template_id'=>$templateRec->template_id,
                'template_name'=>$templateRec->template_name,
                'template_dir_id'=>$templateDirRec->template_dir_id,
                'template_dir_name'=>$templateDirRec->template_dir_name,
                'brand_id'=>$brandRec->brand_id,
                'brand_name'=>$brandRec->brand_name,
                'brand_weixin'=>$brandRec->brand_weixin,
                'brand_weixinqr_path'=>$brandRec->brand_weixinqr_path,
                'brand_icon_path'=>$brandRec->brand_icon_path,
                'defines'=>$def

            ];
            //把扩展参数合并到所有的参数里中去。
            /*$sh = array_merge($domainRec, $pageRec, $brandRec, $templateRec, $templateDirRec, $def);*/
            //dump($sh);
            //输出所有参数到模板，使用$sh调用。
            $this->assign('sh', $sh);
            $this->assign('tongji_id', $tongjiID);
            $this->assign('keyword',$keyword);
            $this->assign('searchKeyword',$searchKeyword);
            $this->assign('shirooRestrictedArea', !empty($domainDB['domain_restricted_area']) ? $domainDB['domain_restricted_area'] : '没有设置地区');

            //这里只是去掉了文件后缀.html，因为带后缀$this->fetch会出错。
            $str = $sh['template_name'];
            $pos = strripos($str, '.');
            $str = substr($str, 0, $pos);
            //动态改变一下模板路径。
            $this->view->config('view_path', $_SERVER['DOCUMENT_ROOT'] . '/public/static/template/');
            //输出相应的模板视图
            return $this->fetch($sh['template_dir_name'] . '/' . $str);
        }
    }
}
