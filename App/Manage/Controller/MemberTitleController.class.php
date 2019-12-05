<?php
/**
 * 会员职务管理
 * Class MemberTitleController.class.php
 * @package Manage\Controller
 * User: czx
 * Date: 2019-01-12 20:22:33
 * Update: 2019-01-12 20:22:33
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */

namespace Manage\Controller;

class MemberTitleController extends CommonController
{
    /**
     * 会员职务列表
     * User: hjun
     * Date: 2019-01-20 16:18:12
     * Update: 2019-01-20 16:18:12
     * Version: 1.00
     */
    public function index()
    {
        $model = D('MemberTitle');
        switch ($this->req['action']) {
            case 'query':
                $where = $model->getMemberTitleListQueryWhere($this->req);
                $data = $model->getMemberTitleListData($this->req['page'], $this->req['limit'], $where);
                $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
                break;
            default:
                $this->display('list');
                exit;
                break;
        }
    }

    /**
     * 添加会员职务
     * User: hjun
     * Date: 2019-01-20 16:18:31
     * Update: 2019-01-20 16:18:31
     * Version: 1.00
     */
    public function add()
    {
        if (IS_POST) {
            $result = D('MemberTitle')->addMemberTitle($this->req);
            if (isSuccess($result)) {
                addAdminLog($this->admin, "新增会员职务,会员职务ID:{$result['data']['title']}");
            }
            $this->apiResponse($result);
        }
    }

    /**
     * 更新会员职务
     * User: hjun
     * Date: 2019-01-20 16:18:40
     * Update: 2019-01-20 16:18:40
     * Version: 1.00
     */
    public function update()
    {
        $model = D('MemberTitle');
        if (IS_GET) {
            switch ($this->req['action']) {
                case 'get':
                    $data = $model->getMemberTitle($this->req['title_id']);
                    $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
                    break;
                default:
                    exit('error');
                    break;
            }
        }
        if (IS_POST) {
            switch ($this->req['action']) {
                case 'update':
                    $result = $model->updateMemberTitle($this->req);
                    $desc = "修改会员职务,会员职务ID:{$result['data']['title']}";
                    break;
                case 'changeSort':
                    $result = $model->changeSort($this->req);
                    $desc = "修改会员职务排序,会员职务ID:{$result['data']['title']},排序:{$result['data']['sort']}";
                    break;
                case 'changeShow':
                    $result = $model->changeShow($this->req);
                    $desc = "修改会员职务显示状态,会员职务ID:{$result['data']['title']},显示:{$result['data']['is_show']}";
                    break;
                default:
                    break;
            }
            if (isSuccess($result)) {
                addAdminLog($this->admin, $desc);
            }
            $this->apiResponse($result);
        }
    }

    /**
     * 删除会员职务
     * User: hjun
     * Date: 2019-01-20 16:18:45
     * Update: 2019-01-20 16:18:45
     * Version: 1.00
     */
    public function delete()
    {
        if (IS_POST) {
            $model = D('MemberTitle');
            $result = $model->deleteMemberTitle($this->req);
            if (isSuccess($result)) {
                addAdminLog($this->admin, "删除会员职务,会员职务ID:{$result['data']['title']}");
            }
            $this->apiResponse($result);
        }
    }
}
