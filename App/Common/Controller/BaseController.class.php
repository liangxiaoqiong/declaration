<?php

namespace Common\Controller;

use Think\Build;
use Think\Controller;

/**
 * 公共基础控制器 所有模块内的基础控制器都继承该控制器
 * 这里主要做一些公共的事
 * Class BaseController
 * @package Common\Controller
 * User: hjun
 * Date: 2018-03-29 01:17:57
 * Update: 2018-03-29 01:17:57
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
class BaseController extends Controller\RestController
{

    // 允许的域名
    protected $allow_origin = [
        'http://www.sosoapi.com',
    ];

    // 请求参数
    protected $req = null;

    /**
     * 初始化
     * BaseController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        header('Content-Type:text/html; charset=utf-8');

        // 请求身份认证
        $this->auth();

        $this->req = $this->getReqParam();
        logWrite("请求参数:" . json_encode($this->req, JSON_UNESCAPED_UNICODE));

        $this->assignPublicParam();

        $this->build();
    }

    /**
     * 身份认证
     * User: hjun
     * Date: 2018-03-31 23:16:21
     * Update: 2018-03-31 23:16:21
     * Version: 1.00
     */
    protected function auth()
    {
        // 跨域访问以及session的保持
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        if (in_array($origin, $this->allow_origin)) {
            header("Access-Control-Allow-Headers:x-requested-with,content-type");
            header('Access-Control-Allow-Credentials: true'); // 控制是否开启与Ajax的Cookie提交方式'
            header('Access-Control-Allow-Origin:' . $origin);
        }
        if (REQUEST_METHOD === 'OPTIONS') {
            $this->apiResponse(getReturn(CODE_SUCCESS));
        }
        // 跨域访问时需要携带sessionID
        $sessionId = I('get.token');
        // 先TP中已经自动session_start() 当时的session_id与跨域的不同 所以需要先销毁
        if (!empty($sessionId)) {
            session_destroy();
            session_id($sessionId);
            session_start();
        }
    }

    /**
     * 赋值一些通用的参数
     * User: hjun
     * Date: 2018-05-30 12:05:40
     * Update: 2018-05-30 12:05:40
     * Version: 1.00
     */
    public function assignPublicParam()
    {
        $this->assign('req', $this->req);
        $this->assign('reqJson', json_encode($this->req));
        $this->assign('NOW_STR', date('Y-m-d'));
        $this->assign('NEXT_YEAR_STR', date('Y-m-d', strtotime('+1 year')));
        $this->assign('V', EXTRA_VERSION);
        $this->assign('ROOT', __APP__);
    }

    /**
     * 获取当前请求参数
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2018-04-01 01:42:55
     * Update: 2018-04-01 01:42:55
     * Version: 1.00
     */
    protected function getReqParam()
    {
        if (isset($this->req)) {
            return $this->req;
        }
        $get = I('get.');
        $post = I('post.');
        $req = array_merge($get, $post);
        $body = I('put.');
        return array_merge($req, $body);
    }

    /**
     * 根据入口文件定义的常量生成目录结构
     * User: hjun
     * Date: 2018-03-31 23:14:43
     * Update: 2018-03-31 23:14:43
     * Version: 1.00
     */
    protected function build()
    {
        // 生成控制器
        if (defined('BUILD_CONTROLLER_LIST')) {
            // 自动生成的控制器列表（注意大小写）
            $list = explode(',', BUILD_CONTROLLER_LIST);
            foreach ($list as $controller) {
                Build::buildController(MODULE_NAME, $controller);
                $dir = APP_PATH . MODULE_NAME . '/View/' . $controller . '/';
                // 生成视图目录
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                // 写入默认视图文件
                $file = $dir . 'index.html';
                if (!is_file($file)) {
                    file_put_contents($file, '<h1>' . MODULE_NAME . '/' . $controller . '/index</h1>');
                }
            }
        }
        // 生成模块的模型
        if (defined('BUILD_MODEL_LIST')) {
            // 自动生成的控制器列表（注意大小写）
            $list = explode(',', BUILD_MODEL_LIST);
            foreach ($list as $model) {
                Build::buildModel(MODULE_NAME, $model);
            }
        }
    }

    /*将数据格式化成json串*/
    public function formatJson($data = array(), $status = 200, $msg = '')
    {
        $result = array();
        $result['code'] = $status;
        $result['msg'] = $msg;
        if (empty($data)) {
            $result['data'] = (object)[];
        } else {
            $result['data'] = $data;
        }

        if (array_key_exists('lists', $data)) {
            $result['data']['num'] = count($data['lists']);
        }
        return json_encode($result, 256);
    }

    /**
     * 响应函数。默认使用JSON
     * @param $data
     * @param string $type
     * @param int $code HTTP状态
     * @param int $json_option json编码选项
     * Author: hj
     * Desc:
     * Date: 2017-11-23 19:28:36
     * Update: 2017-11-23 19:28:36
     * Version: 1.0
     */
    protected function apiResponse($data, $type = 'json', $code = 200, $json_option = 256)
    {
        $this->sendHttpStatus($code);
        if (empty($data)) $data = '';
        if (is_array($data)) {
            $data['time'] = G('a', 'b') . 's';
        }
        if ('json' == $type) {
            // 返回JSON数据格式到客户端 包含状态信息
            $data = json_encode($data, $json_option);
        } elseif ('xml' == $type) {
            // 返回xml格式数据
            $data = xml_encode($data);
        } elseif ('php' == $type) {
            $data = serialize($data);
        }// 默认直接输出
        $this->setContentType($type);
        exit($data);
    }

    /**
     * 重写redirect
     * @param string $url
     * @param array $params
     * @param int $delay
     * @param string $msg
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2018-03-31 23:50:54
     * Update: 2018-03-31 23:50:54
     * Version: 1.00
     */
    public function redirect($url, $params = array(), $delay = 0, $msg = '')
    {
        if (IS_AJAX) {
            $url = U($url, $params);
            $this->apiResponse(getReturn(CODE_REDIRECT, L('_OPERATION_SUCCESS_'), $url));
        } else {
            parent::redirect($url, $params, $delay, $msg);
        }
    }

    /**
     * 重写success
     * @param string $message
     * @param string $jumpUrl
     * @param bool $ajax
     * User: hjun
     * Date: 2018-03-31 23:30:17
     * Update: 2018-03-31 23:30:17
     * Version: 1.00
     */
    public function success($message = '', $jumpUrl = '', $ajax = false)
    {
        if (IS_AJAX) {
            $this->apiResponse(['info' => $message, 'status' => 1, 'url' => $jumpUrl]);
        } else {
            parent::success($message, $jumpUrl, $ajax); // TODO: Change the autogenerated stub
        }
    }

    /**
     * 重写error
     * @param string $message
     * @param string $jumpUrl
     * @param bool $ajax
     * User: hjun
     * Date: 2018-03-31 23:30:17
     * Update: 2018-03-31 23:30:17
     * Version: 1.00
     */
    public function error($message = '', $jumpUrl = '', $ajax = false)
    {
        if (IS_AJAX) {
            $this->apiResponse(['info' => $message, 'status' => 0, 'url' => $jumpUrl, 'code' => CODE_ERROR, 'msg' => $message, 'data' => ['url' => $jumpUrl]]);
        } else {
            parent::error($message, $jumpUrl, $ajax); // TODO: Change the autogenerated stub
        }
    }
}
