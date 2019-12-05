<?php

namespace Wap\Controller;
class AdvertisementController extends BaseController
{
    /**
     * 获取广告轮播图
     * User: czx
     * Date: 2019-01-22 01:25:45
     * Update: 2019-01-22 01:25:45
     * Version: 1.00
     */
    public function getAdData(){
       $adData = D("AdvertiseMent")->getWapAdData();
       $this->ajaxReturn($adData);
    }
}