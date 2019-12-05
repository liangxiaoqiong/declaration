<?php

namespace Common\Model;

use Common\Interfaces\Action;

/**
 * 专家团队
 * Class ExpertModel.class.php
 * @package Common\Model
 * User: hjun
 * Date: 2018-03-29 23:10:07
 * Update: 2018-03-29 23:10:07
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
class ExpertModel extends BaseModel implements Action
{
    protected $tableName = 'expert';

    /**
     * 获取专家列表的筛选条件
     * @param array $request
     * @return array
     * User: hjun
     * Date: 2019-01-20 16:31:44
     * Update: 2019-01-20 16:31:44
     * Version: 1.00
     */
    public function getExpertListQueryWhere($request = [])
    {
        $where = [];
        // 专家姓名
        if (!empty($request['expert_name'])) {
            $where['a.expert_name'] = ['LIKE', "%{$request['expert_name']}%"];
        }
        // 专家职称
        if (!empty($request['expert_title'])) {
            $where['a.expert_title'] = ['LIKE', "%{$request['expert_title']}%"];
        }
        // 显示状态
        if (!empty($request['is_show'])) {
            $where['a.is_show'] = $request['is_show'] - 1;
        }
        // 创建时间
        $result = getRangeWhere($request, 'min_create_time', 'max_create_time');
        $createTimeWhere = $result['data'];
        if (!empty($createTimeWhere)) {
            $where['a.create_time'] = $createTimeWhere;
        }
        return $where;
    }

    /**
     * 获取专家列表
     * @param int $page
     * @param int $limit
     * @param array $queryWhere
     * @return array
     * User: hjun
     * Date: 2019-01-20 17:10:41
     * Update: 2019-01-20 17:10:41
     * Version: 1.00
     */
    public function getExpertListData($page = DF_PAGE, $limit = DF_PAGE_LIMIT, $queryWhere = [])
    {
        $where = [];
        $where['a.is_delete'] = NOT_DELETED;
        $where = array_merge($where, $queryWhere);
        $order = 'a.sort ASC,a.create_time DESC';
        $options = [];
        $options['alias'] = 'a';
        $options['where'] = $where;
        $options['page'] = $page;
        $options['limit'] = $limit;
        $options['order'] = $order;
        $total = $this->getCount($options);
        if (empty($total)) {
            $list = [];
        } else {
            $list = $this->queryList($options);
        }
        $data['total'] = $total;
        $data['list'] = $list;
        return $data;
    }

    /**
     * 获取专家信息
     * @param int $expertId
     * @return array
     * User: hjun
     * Date: 2019-01-20 14:53:10
     * Update: 2019-01-20 14:53:10
     * Version: 1.00
     */
    public function getExpert($expertId = 0)
    {
        $where = [];
        $where['expert_id'] = $expertId;
        $where['is_delete'] = NOT_DELETED;
        $options = [];
        $options['where'] = $where;
        return $this->queryRow($options);
    }

    /**
     * 只查询一次的获取专家
     * @param int $expertId
     * @return array
     * User: hjun
     * Date: 2019-01-20 14:55:10
     * Update: 2019-01-20 14:55:10
     * Version: 1.00
     */
    public function getExpertFromCache($expertId = 0)
    {
        static $info;
        if (isset($info)) {
            return $info;
        }
        $info = $this->getExpert($expertId);
        return $info;
    }

    /**
     * 验证专家
     * @param int $expertId
     * @return boolean
     * User: hjun
     * Date: 2019-01-20 14:55:27
     * Update: 2019-01-20 14:55:27
     * Version: 1.00
     */
    public function validateExpert($expertId = 0)
    {
        $info = $this->getExpertFromCache($expertId);
        if (empty($info)) {
            $this->setValidateError("当前专家已失效");
            return false;
        }
        return true;
    }

    /**
     * 获取操作字段
     * @param int $action
     * @param array $request
     * @return array
     * User: hjun
     * Date: 2019-01-13 13:33:25
     * Update: 2019-01-13 13:33:25
     * Version: 1.00
     */
    public function getFieldsByAction($action = EXPERT_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case EXPERT_ACTION_ADD:
                $result = [
                    'expert_name', 'expert_logo', 'expert_title',
                    'expert_desc', 'sort', 'is_show', 'create_time', 'update_time',
                ];
                break;
            case EXPERT_ACTION_UPDATE:
                $result = [
                    'expert_name', 'expert_logo', 'expert_title',
                    'expert_desc', 'sort', 'is_show', 'update_time',
                ];
                break;
            case EXPERT_ACTION_DELETE:
                $result = ['is_delete', 'delete_time'];
                break;
            case EXPERT_ACTION_CHANGE_SORT:
                $result = ['sort'];
                break;
            case EXPERT_ACTION_CHANGE_SHOW:
                $result = ['is_show'];
                break;
            default:
                $result = [];
                break;
        }
        return $result;
    }

    /**
     * 根据操作获取验证规则
     * @param int $action
     * @param array $request
     * @return array
     * User: hjun
     * Date: 2019-01-13 14:55:28
     * Update: 2019-01-13 14:55:28
     * Version: 1.00
     */
    public function getValidateByAction($action = EXPERT_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case EXPERT_ACTION_ADD:
                $result = [
                    ['expert_name', 'require', "请输入专家姓名", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['expert_title', 'require', "请输入专家职称", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['expert_logo', 'url', "请上传专家照片", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['expert_desc', 'require', "请输入专家简介", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['is_show', '/^[0|1]$/', "请设置专家是否显示", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['sort', 'number', "请设置专家排序", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                ];
                break;
            case EXPERT_ACTION_UPDATE:
                $result = [
                    ['expert_id', 'validateExpert', "专家已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['expert_name', 'require', "请输入专家姓名", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['expert_title', 'require', "请输入专家职称", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['expert_logo', 'url', "请上传专家照片", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['expert_desc', 'require', "请输入专家简介", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['is_show', '/^[0|1]$/', "请设置专家是否显示", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['sort', 'number', "请设置专家排序", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                ];
                break;
            case EXPERT_ACTION_DELETE:
                $result = [
                    ['expert_id', 'validateExpert', "专家已删除", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                ];
                break;
            case EXPERT_ACTION_CHANGE_SORT:
                $result = [
                    ['expert_id', 'validateExpert', "专家已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['sort', 'number', "请设置专家排序", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                ];
                break;
            case EXPERT_ACTION_CHANGE_SHOW:
                $result = [
                    ['expert_id', 'validateExpert', "专家已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['is_show', '/^[0|1]$/', "请设置专家是否显示", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                ];
                break;
            default:
                $result = [];
                break;
        }
        return $result;
    }

    /**
     * 根据操作获取完成规则
     * @param int $action
     * @param array $request
     * @return array
     * User: hjun
     * Date: 2019-01-13 15:06:04
     * Update: 2019-01-13 15:06:04
     * Version: 1.00
     */
    public function getAutoByAction($action = EXPERT_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case EXPERT_ACTION_ADD:
                $result = [
                    ['create_time', NOW_TIME, self::MODEL_INSERT, 'string'],
                    ['update_time', NOW_TIME, self::MODEL_INSERT, 'string'],
                ];
                break;
            case EXPERT_ACTION_UPDATE:
                $result = [
                    ['create_time', NOW_TIME, self::MODEL_UPDATE, 'string'],
                ];
                break;
            case EXPERT_ACTION_DELETE:
                $result = [
                    ['is_delete', DELETED, self::MODEL_UPDATE, 'string'],
                    ['delete_time', NOW_TIME, self::MODEL_UPDATE, 'string'],
                ];
                break;
            case EXPERT_ACTION_CHANGE_SORT:
            case EXPERT_ACTION_CHANGE_SHOW:
            default:
                $result = [];
                break;
        }
        return $result;
    }

    /**
     * 根据操作获取 数据库操作类型
     * @param int $action
     * @param array $request
     * @return int
     * User: hjun
     * Date: 2019-01-13 15:06:26
     * Update: 2019-01-13 15:06:26
     * Version: 1.00
     */
    public function getTypeByAction($action = EXPERT_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case EXPERT_ACTION_ADD:
                $result = self::MODEL_INSERT;
                break;
            case EXPERT_ACTION_UPDATE:
            case EXPERT_ACTION_DELETE:
            case EXPERT_ACTION_CHANGE_SORT:
            case EXPERT_ACTION_CHANGE_SHOW:
            default:
                $result = self::MODEL_UPDATE;
                break;
        }
        return $result;
    }

    /**
     * 根据操作获取描述
     * @param int $action
     * @param array $request
     * @return string
     * User: hjun
     * Date: 2019-01-13 18:26:59
     * Update: 2019-01-13 18:26:59
     * Version: 1.00
     */
    public function getDescByAction($action = EXPERT_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case EXPERT_ACTION_ADD:
                $result = '新增专家';
                break;
            case EXPERT_ACTION_UPDATE:
                $result = '修改专家';
                break;
            case EXPERT_ACTION_DELETE:
                $result = '删除专家';
                break;
            case EXPERT_ACTION_CHANGE_SORT:
                $result = '修改专家排序';
                break;
            case EXPERT_ACTION_CHANGE_SHOW:
                $result = '修改专家是否显示';
                break;
            default:
                $result = '修改专家';
                break;
        }
        return $result;
    }

    /**
     * 操作
     * @param int $action
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-13 14:44:47
     * Update: 2019-01-13 14:44:47
     * Version: 1.00
     */
    public function action($action = EXPERT_ACTION_ADD, $request = [])
    {
        $fields = $this->getFieldsByAction($action, $request);
        $validate = $this->getValidateByAction($action, $request);
        $auto = $this->getAutoByAction($action, $request);
        $type = $this->getTypeByAction($action, $request);
        $result = $this->getAndValidateDataFromRequest($fields, $request, $validate, $auto, $type);
        if (!isSuccess($result)) {
            return $result;
        }
        $data = $result['data'];

        $this->startTrans();
        $results = [];
        if ($type == self::MODEL_INSERT) {
            $data['expert_id'] = $this->add($data);
            $results[] = $data['expert_id'];
        } elseif ($type == self::MODEL_UPDATE) {
            $data['expert_id'] = $request['expert_id'];
            $results[] = $this->save($data);
        } elseif ($type == self::MODEL_DELETE) {
            $data['expert_id'] = $request['expert_id'];
            $results[] = $this->where(['expert_id' => $data['expert_id']])->delete();
        } else {
            $results[] = false;
        }
        if (in_array(false, $results, true)) {
            $this->rollback();
            return getReturn(CODE_ERROR);
        }
        $this->commit();
        return getReturn(CODE_SUCCESS, 'success', $data);
    }

    /**
     * 新增专家
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:01
     * Update: 2019-01-20 15:04:01
     * Version: 1.00
     */
    public function addExpert($request = [])
    {
        $result = $this->action(EXPERT_ACTION_ADD, $request);
        if (isSuccess($result)) {
            $result['msg'] = "新增成功";
        }
        return $result;
    }

    /**
     * 修改专家
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:08
     * Update: 2019-01-20 15:04:08
     * Version: 1.00
     */
    public function updateExpert($request = [])
    {
        $result = $this->action(EXPERT_ACTION_UPDATE, $request);
        if (isSuccess($result)) {
            $result['msg'] = "修改成功";
        }
        return $result;
    }

    /**
     * 改变排序
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:13
     * Update: 2019-01-20 15:04:13
     * Version: 1.00
     */
    public function changeSort($request = [])
    {
        $result = $this->action(EXPERT_ACTION_CHANGE_SORT, $request);
        if (isSuccess($result)) {
            $result['msg'] = "修改成功";
        }
        return $result;
    }

    /**
     * 修改是否显示
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:23
     * Update: 2019-01-20 15:04:23
     * Version: 1.00
     */
    public function changeShow($request = [])
    {
        $result = $this->action(EXPERT_ACTION_CHANGE_SHOW, $request);
        if (isSuccess($result)) {
            $result['msg'] = "修改成功";
        }
        return $result;
    }

    /**
     * 删除专家
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:30
     * Update: 2019-01-20 15:04:30
     * Version: 1.00
     */
    public function deleteExpert($request = [])
    {
        $result = $this->action(EXPERT_ACTION_DELETE, $request);
        if (isSuccess($result)) {
            $result['msg'] = "删除成功";
        }
        return $result;
    }

    /**
     * 获取首页数据
     * @return array
     * User: hjun
     * Date: 2019-01-23 20:59:51
     * Update: 2019-01-23 20:59:51
     * Version: 1.00
     */
    public function getIndexLinkList()
    {
        $field = [
            'expert_id', 'expert_name', 'expert_logo', 'expert_desc', 'expert_title'
        ];
        $field = implode(',', $field);
        $where = [];
        $where['is_show'] = 1;
        $where['is_delete'] = NOT_DELETED;
        $order = 'sort ASC';
        $options = [];
        $options['field'] = $field;
        $options['where'] = $where;
        $options['order'] = $order;
        return $this->queryList($options);
    }
}