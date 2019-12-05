<?php
/**
 * 官网导航
 * Class NavController.class.php
 * @package Manage\Controller
 * User: czx
 * Date: 2019-01-12 20:22:33
 * Update: 2019-01-12 20:22:33
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */

namespace Manage\Controller;

class NavController extends CommonController
{
    /**
     * 导航列表
     * User: hjun
     * Date: 2019-01-20 16:39:53
     * Update: 2019-01-20 16:39:53
     * Version: 1.00
     */
    public function index()
    {
        switch ($this->req['action']) {
            case 'query':
                $this->getNavData();
                break;
            case 'queryParent':
                $this->getParentNav();
                break;
            default:
                $this->display('list');
                exit;
                break;
        }
    }

    /**
     * 新增导航
     * User: hjun
     * Date: 2019-01-20 16:40:29
     * Update: 2019-01-20 16:40:29
     * Version: 1.00
     */
    public function add()
    {
        $request = $this->req;
        $checkData = $this->checkNavAdd($request);
        if ($checkData['code'] != 200) {
            $this->ajaxReturn($checkData);
        }
        $returnData = D("Nav")->navAdd($request);
        $this->ajaxReturn($returnData);
    }

    /**
     * 检查导航栏信息
     * @param $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: czx
     * Date: 2019-01-21 23:04:51
     * Update: 2019-01-21 23:04:51
     * Version: 1.00
     */
    public function checkNavAdd($request)
    {
        if (empty($request['nav_name'])) return getReturn(-1, '导航栏名称不能为空');
        return getReturn(200, '成功');
    }

    /**
     * 修改导航
     * User: hjun
     * Date: 2019-01-20 16:40:35
     * Update: 2019-01-20 16:40:35
     * Version: 1.00
     */
    public function update()
    {
        $request = $this->req;
        $checkData = $this->checkNavUpdate($request);
        if ($checkData['code'] != 200) {
            $this->ajaxReturn($checkData);
        }
        $returnData = D("Nav")->navUpdate($request);
        $this->ajaxReturn($returnData);
    }

    /**
     * 检查导航栏更新信息
     * @param $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: czx
     * Date: 2019-01-21 23:04:51
     * Update: 2019-01-21 23:04:51
     * Version: 1.00
     */
    public function checkNavUpdate($request)
    {
        if (empty($request['nav_name'])) return getReturn(-1, '导航栏名称不能为空');
        if (empty($request['nav_id'])) return getReturn(-2, '导航栏编号不能为空');
        return getReturn(200, '成功');
    }

    /**
     * 删除导航
     * User: hjun
     * Date: 2019-01-20 16:40:40
     * Update: 2019-01-20 16:40:40
     * Version: 1.00
     */
    public function delete()
    {
        $request = $this->req;
        $checkData = $this->checkNavDelete($request);
        if ($checkData['code'] != 200) {
            $this->ajaxReturn($checkData);
        }
        $returnData = D("Nav")->navDelete($request);
        $this->ajaxReturn($returnData);
    }


    /**
     * 删除导航栏信息
     * @param $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: czx
     * Date: 2019-01-21 23:04:51
     * Update: 2019-01-21 23:04:51
     * Version: 1.00
     */
    public function checkNavDelete($request)
    {
        if (empty($request['nav_id'])) return getReturn(-1, '导航栏编号不能为空');
        return getReturn(200, '成功');
    }

    /**
     *获取导航列表
     * User: czx
     * Date: 2019-01-20 21:34:17
     * Update: 2019-01-20 21:34:17
     * Version: 1.00
     */
    public function getNavData()
    {
        $returnData = D("Nav")->getData();
        $this->ajaxReturn($returnData);
    }

    /**
     * 获取父类的导航
     * User: czx
     * Date: 2019-01-21 23:17:22
     * Update: 2019-01-21 23:17:22
     * Version: 1.00
     */
    public function getParentNav()
    {
        $returnData = D("Nav")->getParentNav();
        $this->ajaxReturn($returnData);
    }
}
