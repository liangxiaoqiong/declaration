<?php
/**
 * 专家管理
 * Class ExpertController.class.php
 * @package Manage\Controller
 * User: czx
 * Date: 2019-01-12 20:22:33
 * Update: 2019-01-12 20:22:33
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */

namespace Manage\Controller;

class ExpertController extends CommonController
{
    /**
     * 友情专家
     * User: hjun
     * Date: 2019-01-20 16:39:53
     * Update: 2019-01-20 16:39:53
     * Version: 1.00
     */
    public function index()
    {
        $model = D('Expert');
        switch ($this->req['action']) {
            case 'query':
                $where = $model->getExpertListQueryWhere($this->req);
                $data = $model->getExpertListData($this->req['page'], $this->req['limit'], $where);
                $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
                break;
            default:
                $this->display('list');
                exit;
                break;
        }
    }

    /**
     * 新增专家
     * User: hjun
     * Date: 2019-01-20 16:40:29
     * Update: 2019-01-20 16:40:29
     * Version: 1.00
     */
    public function add()
    {
        if (IS_POST) {
            switch ($this->req['action']) {
                case 'add':
                    $result = D('Expert')->addExpert($this->req);
                    $desc = "新增专家,专家ID:{$result['data']['expert_id']}";
                    break;
                default:
                    exit('error');
                    break;
            }
            if (isSuccess($result)) {
                addAdminLog($this->admin, $desc);
            }
            $this->apiResponse($result);
        }
    }

    /**
     * 修改专家
     * User: hjun
     * Date: 2019-01-20 16:40:35
     * Update: 2019-01-20 16:40:35
     * Version: 1.00
     */
    public function update()
    {
        $model = D('Expert');
        if (IS_GET) {
            switch ($this->req['action']) {
                case 'get':
                    $data = $model->getExpert($this->req['expert_id']);
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
                    $result = $model->updateExpert($this->req);
                    $desc = "修改专家,专家ID:{$result['data']['expert_id']}";
                    break;
                case 'changeSort':
                    $result = $model->changeSort($this->req);
                    $desc = "修改专家排序,专家ID:{$result['data']['expert_id']},排序:{$result['data']['sort']}";
                    break;
                case 'changeShow':
                    $result = $model->changeShow($this->req);
                    $desc = "修改专家显示状态,专家ID:{$result['data']['expert_id']},显示状态:{$result['data']['is_show']}";
                    break;
                default:
                    exit('error');
                    break;
            }
            if (isSuccess($result)) {
                addAdminLog($this->admin, $desc);
            }
            $this->apiResponse($result);
        }
    }

    /**
     * 删除专家
     * User: hjun
     * Date: 2019-01-20 16:40:40
     * Update: 2019-01-20 16:40:40
     * Version: 1.00
     */
    public function delete()
    {
        $model = D('Expert');
        if (IS_POST) {
            switch ($this->req['action']) {
                case 'delete':
                    $result = $model->deleteExpert($this->req);
                    $desc = "删除专家,专家ID:{$result['data']['expert_id']}";
                    break;
                default:
                    exit('error');
                    break;
            }
            if (isSuccess($result)) {
                addAdminLog($this->admin, $desc);
            }
            $this->apiResponse($result);
        }
    }
}
