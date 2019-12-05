<?php

namespace Wap\Controller;
class MemberController extends BaseController
{
    /**
     * 显示会员
     * User: hjun
     * Date: 2019-01-21 23:26:00
     * Update: 2019-01-21 23:26:00
     * Version: 1.00
     */
    public function member()
    {
        $model = D('Member');
        switch ($this->req['action']) {
            // 获取某个会员的数据
            case 'get':
                $data = $model->getMemberWithTitle($this->req['member_id']);
                $this->apiResponse(getReturn(CODE_SUCCESS, '', $data));
                break;
            // 获取首页会员列表
            case 'query':
                $data = $model->getIndexMemberList();
                $this->apiResponse(getReturn(CODE_SUCCESS, '', $data));
                break;
            // 获取会员职务及其列表
            case 'getMemberTitleData':
                $data = $model->getMemberTitleData();
                $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
                break;
            default:
                $this->display();
                break;
        }
    }
}