<?php

namespace Wap\Controller;
class NavController extends BaseController
{
    /**
     * 获取导航菜单栏
     * User: czx
     * Date: 2019-01-22 01:12:12
     * Update: 2019-01-22 01:12:12
     * Version: 1.00
     */
    public function getNavData()
    {
        $navData = D("Nav")->getWapNavData();
        $this->ajaxReturn($navData);
    }

    /**
     * 获取首页推荐文章
     * User: hjun
     * Date: 2019-01-22 21:42:40
     * Update: 2019-01-22 21:42:40
     * Version: 1.00
     */
    public function navArticle()
    {
        switch ($this->req['action']) {
            case 'query':
                $data = D('Nav')->getRecommendNavAndArt();
                $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
                break;
            default:
                break;
        }
    }
}