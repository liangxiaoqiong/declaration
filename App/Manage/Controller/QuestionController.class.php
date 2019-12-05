<?php
/**
 * 问卷调查
 * Class QuestionController
 * @package Manage\Controller
 * User: czx
 * Date: 2019-01-12 20:22:33
 * Update: 2019-01-12 20:22:33
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */

namespace Manage\Controller;

class QuestionController extends CommonController
{
    /**
     * 问卷列表
     * User: hjun
     * Date: 2019-01-12 20:13:02
     * Update: 2019-01-12 20:13:02
     * Version: 1.00
     */
    public function qnrList()
    {
        if ($this->req['action'] === 'query') {
            // 获取列表
            $model = D('Qnr');
            $where = $model->getQnrListQueryWhere($this->req);
            $data = $model->getQnrListData($this->req['page'], $this->req['limit'], $where);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        } elseif ($this->req['action'] === 'detail') {
            // 获取列表
            $model = D('QnrAnswer');
            $where = $model->getAnswerListQueryWhere($this->req);
            $data = $model->getAnswerListData($this->req['page'], $this->req['limit'], $where);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        } elseif ($this->req['action'] === 'get') {
            $data['answer_data'] = D('QnrAnswer')->getAnswer($this->req['answer_id']);
            $data['qnr_data'] = D('Qnr')->getQnr($data['answer_data']['qnr_id']);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        }
        $this->display();
    }

    /**
     * 新增问卷
     * User: hjun
     * Date: 2019-01-12 20:14:33
     * Update: 2019-01-12 20:14:33
     * Version: 1.00
     */
    public function addQnr()
    {
        if (IS_POST) {
            $result = D('Qnr')->addQnr($this->req);
            if (isSuccess($result)) {
                addAdminLog($this->admin, "新增问卷，问卷ID:{$result['data']['qnr_id']}");
            }
            $this->apiResponse($result);
        }
    }

    /**
     * 修改问卷
     * User: hjun
     * Date: 2019-01-12 20:14:44
     * Update: 2019-01-12 20:14:44
     * Version: 1.00
     */
    public function updateQnr()
    {
        if (IS_POST) {
            if ($this->req['action'] === 'save') {
                // 修改问卷
                $result = D('Qnr')->updateQnr($this->req);
                $desc = "修改问卷,问卷ID:{$result['data']['qnr_id']}";
            } elseif ($this->req['action'] === 'changeOpen') {
                // 修改问卷开启状态
                $result = D('Qnr')->changeQnrOpen($this->req);
                $desc = "修改问卷的开启状态,问卷ID:{$result['data']['qnr_id']},开启状态:{$result['data']['is_open']}";
            } elseif ($this->req['action'] === 'changeForce') {
                // 修改问卷的强制状态
                $result = D('Qnr')->changeQnrForce($this->req);
                $desc = "修改问卷的强制状态,问卷ID:{$result['data']['qnr_id']},强制状态:{$result['data']['is_force']}";
            }
            if (isSuccess($result)) {
                addAdminLog($this->admin, $desc);
            }
            $this->apiResponse($result);
        }
        if ($this->req['action'] === 'get') {
            // 获取问卷数据
            $data = D('Qnr')->getQnr($this->req['qnr_id']);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        }
    }
}
