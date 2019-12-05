<?php
/**
 * 广告管理
 * Class AdvertisementController.class.php
 * @package Manage\Controller
 * User: czx
 * Date: 2019-01-12 20:22:33
 * Update: 2019-01-12 20:22:33
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */

namespace Manage\Controller;

class AdvertisementController extends CommonController
{
    /**
     * 广告列表
     * User: hjun
     * Date: 2019-01-20 16:18:12
     * Update: 2019-01-20 16:18:12
     * Version: 1.00
     */
    public function index()
    {
        switch ($this->req['action']) {
            case 'query':
                $this->getAdData();
                break;
            default:
                $this->display('list');
                exit;
                break;
        }
    }

    /**
     * 获取轮播图列表
     * Date: 2019-01-20 21:08:05
     * Update: 2019-01-20 21:08:05
     * Version: 1.00
     */
    public function getAdData()
    {
        $returnData = D("AdvertiseMent")->getList();
        $this->ajaxReturn($returnData);
    }

    /**
     * 添加轮播图列表
     * User: czx
     * Date: 2019-01-20 21:09:06
     * Update: 2019-01-20 21:09:06
     * Version: 1.00
     */
    public function addAd()
    {
        $request = $this->req;
        $checkData = $this->checkAdAdd($request);
        if ($checkData['code'] != 200) {
            $this->ajaxReturn($checkData);
        }
        $returnData = D("AdvertiseMent")->addAd($request);
        $this->ajaxReturn($returnData);
    }

    /**
     * 检查插入广告轮播图
     * @param $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: czx
     * Date: 2019-01-22 00:21:37
     * Update: 2019-01-22 00:21:37
     * Version: 1.00
     */
    public function checkAdAdd($request)
    {
        if (empty($request['ad_img'])) return getReturn(-1, '广告轮播图上传图片不能为空');
        return getReturn(200, '成功');
    }

    /**
     * 更新轮播图列表
     * User: czx
     * Date: 2019-01-20 21:09:06
     * Update: 2019-01-20 21:09:06
     * Version: 1.00
     */
    public function updateAd()
    {
        $request = $this->req;
        $checkData = $this->checkupdateAd($request);
        if ($checkData['code'] != 200) {
            $this->ajaxReturn($checkData);
        }
        $returnData = D("AdvertiseMent")->updateAd($request);
        $this->ajaxReturn($returnData);
    }

    /**
     * 检查更新广告轮播图
     * @param $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: czx
     * Date: 2019-01-22 00:21:37
     * Update: 2019-01-22 00:21:37
     * Version: 1.00
     */
    public function checkupdateAd($request)
    {
        if (empty($request['ad_id'])) return getReturn(-1, '广告轮播图编号不能为空');
        return getReturn(200, '成功');
    }

    /**
     * 删除轮播图列表
     * User: czx
     * Date: 2019-01-20 21:09:06
     * Update: 2019-01-20 21:09:06
     * Version: 1.00
     */
    public function deleteAd()
    {
        $request = $this->req;
        $checkData = $this->checkupdateAd($request);
        if ($checkData['code'] != 200) {
            $this->ajaxReturn($checkData);
        }
        $returnData = D("AdvertiseMent")->deleteAd($request);
        $this->ajaxReturn($returnData);
    }
}
