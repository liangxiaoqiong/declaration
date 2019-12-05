<?php

namespace Common\Model;

/**
 * 应用领域
 * Class ApplyFieldsModel
 * @package Common\Model
 * User: hjun
 * Date: 2018-03-29 23:10:07
 * Update: 2018-03-29 23:10:07
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
class ApplyFieldsModel extends BaseModel
{
    /**
     * 获取选择项数据
     * @return array
     * User: hjun
     * Date: 2019-01-12 20:01:43
     * Update: 2019-01-12 20:01:43
     * Version: 1.00
     */
    public function getSelectList()
    {
        $list = S('apply_fields_select_list');
        if (empty($list)) {
            $where = [];
            $where['is_delete'] = NOT_DELETED;
            $field = ['id', 'item_name'];
            $options = [];
            $options['field'] = $field;
            $options['where'] = $where;
            $list = $this->queryList($options);
            S('apply_fields_select_list', $list);
        }
        return $list;
    }
}