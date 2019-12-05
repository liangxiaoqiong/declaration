<?php

namespace Wap\Controller;
class LinkController extends BaseController
{
    /**
     * 显示友情链接
     * User: hjun
     * Date: 2019-01-21 23:26:00
     * Update: 2019-01-21 23:26:00
     * Version: 1.00
     */
    public function link()
    {
        $model = D('Link');
        switch ($this->req['action']) {
            // 获取某个会员的数据
            case 'get':
                break;
            // 获取首页会员列表
            case 'query':
                $data = $model->getIndexLinkList();
                $this->apiResponse(getReturn(CODE_SUCCESS, '', $data));
                break;
            default:
                $this->display();
                break;
        }
    }
}