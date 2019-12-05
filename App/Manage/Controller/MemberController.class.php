<?php
/**
 * 会员管理
 * Class MemberController.class.php
 * @package Manage\Controller
 * User: czx
 * Date: 2019-01-12 20:22:33
 * Update: 2019-01-12 20:22:33
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */

namespace Manage\Controller;

class MemberController extends CommonController
{
    /**
     * 会员列表
     * User: hjun
     * Date: 2019-01-20 16:18:12
     * Update: 2019-01-20 16:18:12
     * Version: 1.00
     */
    public function index()
    {
        $model = D('Member');
        switch ($this->req['action']) {
            case 'query':
                $where = $model->getMemberListQueryWhere($this->req);
                $data = $model->getMemberListData($this->req['page'], $this->req['limit'], $where);
                $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
                break;
            default:
                $this->display('list');
                exit;
                break;
        }
    }

    /**
     * 添加会员
     * User: hjun
     * Date: 2019-01-20 16:18:31
     * Update: 2019-01-20 16:18:31
     * Version: 1.00
     */
    public function add()
    {
        if (IS_POST) {
            $result = D('Member')->addMember($this->req);
            if (isSuccess($result)) {
                addAdminLog($this->admin, "新增会员,会员ID:{$result['data']['member_id']}");
            }
            $this->apiResponse($result);
        }
    }

    /**
     * 更新会员
     * User: hjun
     * Date: 2019-01-20 16:18:40
     * Update: 2019-01-20 16:18:40
     * Version: 1.00
     */
    public function update()
    {
        if (IS_POST) {
            $model = D('Member');
            switch ($this->req['action']) {
                case 'update':
                    $result = $model->updateMember($this->req);
                    $desc = "修改会员,会员ID:{$result['data']['member_id']}";
                    break;
                case 'changeSort':
                    $result = $model->changeSort($this->req);
                    $desc = "修改会员排序,会员ID:{$result['data']['member_id']},排序:{$result['data']['sort']}";
                    break;
                case 'changeRecommend':
                    $result = $model->changeRecommend($this->req);
                    $desc = "修改会员推荐状态,会员ID:{$result['data']['member_id']},推荐:{$result['data']['is_recommend']}";
                    break;
                case 'changeShow':
                    $result = $model->changeShow($this->req);
                    $desc = "修改会员显示状态,会员ID:{$result['data']['member_id']},显示:{$result['data']['is_show']}";
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
     * 删除会员
     * User: hjun
     * Date: 2019-01-20 16:18:45
     * Update: 2019-01-20 16:18:45
     * Version: 1.00
     */
    public function delete()
    {
        if (IS_POST) {
            $model = D('Member');
            $result = $model->deleteMember($this->req);
            if (isSuccess($result)) {
                addAdminLog($this->admin, "删除会员,会员ID:{$result['data']['member_id']}");
            }
            $this->apiResponse($result);
        }
    }
}
