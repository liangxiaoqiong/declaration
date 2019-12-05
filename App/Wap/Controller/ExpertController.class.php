<?php

namespace Wap\Controller;
class ExpertController extends BaseController
{
    /**
     * 显示专家
     * User: hjun
     * Date: 2019-01-21 23:26:00
     * Update: 2019-01-21 23:26:00
     * Version: 1.00
     */
    public function expert()
    {
        $model = D('Expert');
        switch ($this->req['action']) {
            // 获取某个专家的数据
            case 'get':
                $data = $model->getExpert($this->req['expert_id']);
                $this->apiResponse(getReturn(CODE_SUCCESS, '', $data));
                break;
            // 获取首页专家列表
            case 'query':
                $where = [];
                $where['a.is_show'] = 1;
                $data = $model->getExpertListData(1, 0, $where);
                $this->apiResponse(getReturn(CODE_SUCCESS, '', $data['list']));
                break;
            default:
                $this->display();
                break;
        }
    }
}