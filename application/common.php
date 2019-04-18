<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用公共文件
function json_shiroo($code, $msg = '', $count = 0, $data = array(), $data2 = array())
{
    if (!is_string($code)) {
        $arr = ['code' => $code, 'msg' => $msg, 'count' => $count];
    } else {
        $arr = array_merge(err($code), ['count' => $count > 0 ? $count : 0]);
        if (!empty($msg)) {
            $arr['msg'] = $msg;
        }
    }
    $resultData = array_merge($arr, array('data' => $data),$data2);
    return json($resultData);
}

function err($name)
{

    //定义常用返回的数据内容。
    $err = [
        'edit' => [
            'success' => ['code' => 0, 'msg' => '编辑成功。'],
            'error' => ['code' => 100, 'msg' => '编辑失败。']
        ],
        'del' => [
            'success' => ['code' => 0, 'msg' => '删除成功。'],
            'error' => ['code' => 101, 'msg' => '删除失败。']
        ],
        'add' => [
            'success' => ['code' => 0, 'msg' => '新增成功。'],
            'error' => ['code' => 102, 'msg' => '新增失败。']
        ],
        'validate' => [
            'code' => 104,
            'msg' => '参数格式不合法。'
        ],
        'database' => [
            'code' => 105, 'msg' => '操作出现异常，请重试！'
        ],
        'exist' => [
            'code' => 106, 'msg' => '已存在相同记录。'
        ],
        'upload' => [
            'success' => ['code' => 0, 'msg' => '上传成功。'],
            'error' => ['code' => 107, 'msg' => '上传失败。']
        ],
        'login' => [
            'success' => ['code' => 0, 'msg' => '登录成功。'],
            'error' => ['code' => 108, 'msg' => '登录失败。']
        ]
    ];


    //这里判断结尾有没有小数点，有的话就去掉。
    if ('.' == substr($name, -1)) {
        $name = substr($name, 0, -1);
    }
    //这里进行分割传递进来的参数，参数格式：del.success
    $expStr = explode('.', $name);

    $result = 0;//新建一个临时存放数据。
    if (is_array($expStr)) {
        $n = count($expStr);//获取分割后的数组成员数。
        for ($i = 0; $i < $n; $i++) {
            if (is_array($result)) {
                //如果$result临时存放数据是数组数据，
                if (isset($result[$expStr[$i]])) {
                    $result = $result[$expStr[$i]];
                } else {
                    $result = 0;
                    break;
                }
            } else {
                //不是的话，表示第一次赋值，直接取$err一级数组。
                if (isset($err[$expStr[$i]])) {
                    $result = $err[$expStr[$i]];
                } else {
                    $result = 0;
                    break;
                }
            }
        }
    }
    if ($result == 0) {
        $result = ['code' => -1, 'msg' => $name . '不存在。'];
    }
    return $result;
}

function isLogin()
{
    $uid = (int)session('user.user_id');
    if (session('?user') && $uid > 0) {
        return true;
    } else {
        return false;
    }
}

function getUserID()
{
    if (isLogin()) {
        return (int)session('user.user_id');
    } else {
        return 0;
    }
}

function shaPass($password)
{
    return sha1($password);
}

