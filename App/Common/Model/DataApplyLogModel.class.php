<?php
/**
 * 数据报表操作日志
 * Class DataApplyLogModel.class.php
 * @package Common\Model
 * User: hjun
 * Date: 2018-03-29 23:10:34
 * Update: 2018-03-29 23:10:34
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018 All rights reserved
 */

namespace Common\Model;

class DataApplyLogModel extends BaseModel
{
    protected $tableName = 'data_apply_log';

    /**
     * 记录日志
     * @param int $action
     * @param array $oldData
     * @param array $newData
     * @param array $request
     * @param string $desc
     * @return mixed
     * User: hjun
     * Date: 2018-11-27 11:43:37
     * Update: 2018-11-27 11:43:37
     * Version: 1.00
     */
    public function addLog($action = DATA_ACTION_ADD, $oldData = [], $newData = [], $request = [], $desc = '')
    {
        $data = [];
        $data['company_id'] = $newData['company_id'];
        $data['data_id'] = $newData['data_id'];
        $data['action'] = $action;
        $data['desc'] = empty($desc) ? '' : $desc;
        $data['before_action_data'] = jsonEncode($oldData);
        $data['after_action_data'] = jsonEncode($newData);
        $data['create_time'] = NOW_TIME;
        $data['request'] = jsonEncode($request);
        $admin = $this->getAdmin();
        if (!empty($admin)) {
            $data['admin_id'] = $admin['id'];
            $data['admin_name'] = $admin['username'];
        }
        return $this->add($data);
    }
}