<?php

namespace Wap\Controller;
class IndexController extends BaseController
{
    //官网首页
    public function index()
    {
        $this->display();
    }

    public function info()
    {
        $this->display();
    }

    /**
     * 获取首页主页数据
     * User: hjun
     * Date: 2019-01-23 19:37:07
     * Update: 2019-01-23 19:37:07
     * Version: 1.00
     */
    public function getIndexData()
    {
        $data = [];
        $data['ad_data'] = D("AdvertiseMent")->getWapAdData()['data']; // 轮播数据
        $data['nav_art_data'] = D('Nav')->getRecommendNavAndArt(); // 导航文章数据
        $data['expert_data'] = D('Expert')->getIndexLinkList(); // 专家团队
        $data['member_data'] = D('Member')->getIndexMemberList(); // 会员数据
        $data['link_data'] = D('Link')->getIndexLinkList(); // 友情链接
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
    }

}