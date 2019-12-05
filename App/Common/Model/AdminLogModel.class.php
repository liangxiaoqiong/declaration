<?php

namespace Common\Model;

/**
 * 日志
 * Class AdminLogModel
 * @package Common\Model
 * User: hjun
 * Date: 2018-05-25 18:05:39
 * Update: 2018-05-25 18:05:39
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
class AdminLogModel extends BaseModel
{
    /**
     * 日志列表
     * @param int $page
     * @param int $limit
     * @param array $condition
     * @return array
     * User: hjun
     * Date: 2018-05-25 18:08:29
     * Update: 2018-05-25 18:08:29
     * Version: 1.00
     */
    public function logList($page = 1, $limit = 20, $condition = [])
    {
        $where = [];
        $where['is_delete'] = NOT_DELETED;
        $where = array_merge($where, $condition);
        $options = [];
        $options['where'] = $where;
        $totalList = $this->queryList($options);
        $options['page'] = $page;
        $options['limit'] = $limit;
        $options['order'] = 'log_id DESC';
        $list = $this->queryList($options);
        foreach ($list as &$log) {
            $this->auto([
                ['create_time', 'date', self::MODEL_UPDATE, 'function', [DATE_FORMAT . ' H:i:s', $log['create_time']]]
            ]);
            $this->autoOperation($log, self::MODEL_UPDATE);
        }
        $data['total'] = count($totalList);
        $data['list'] = $list;
        return $data;
    }
}