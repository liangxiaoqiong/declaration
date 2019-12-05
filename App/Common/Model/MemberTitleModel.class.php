<?php

namespace Common\Model;

use Common\Interfaces\Action;

/**
 * 会员职务
 * Class MemberTitleModel.class.php
 * @package Common\Model
 * User: hjun
 * Date: 2018-03-29 23:10:07
 * Update: 2018-03-29 23:10:07
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
class MemberTitleModel extends BaseModel implements Action
{
    protected $tableName = 'member_title';

    /**
     * 获取会员职务列表的筛选条件
     * @param array $request
     * @return array
     * User: hjun
     * Date: 2019-01-20 16:31:44
     * Update: 2019-01-20 16:31:44
     * Version: 1.00
     */
    public function getMemberTitleListQueryWhere($request = [])
    {
        $where = [];
        // 职务名称
        if (!empty($request['title_name'])) {
            $where['a.title_name'] = ['LIKE', "%{$request['title_name']}%"];
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
     * 获取会员职务列表
     * @param int $page
     * @param int $limit
     * @param array $queryWhere
     * @return array
     * User: hjun
     * Date: 2019-01-20 17:10:41
     * Update: 2019-01-20 17:10:41
     * Version: 1.00
     */
    public function getMemberTitleListData($page = DF_PAGE, $limit = DF_PAGE_LIMIT, $queryWhere = [])
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
     * 获取会员职务信息
     * @param int $titleId
     * @return array
     * User: hjun
     * Date: 2019-01-20 14:53:10
     * Update: 2019-01-20 14:53:10
     * Version: 1.00
     */
    public function getMemberTitle($titleId = 0)
    {
        $where = [];
        $where['title_id'] = $titleId;
        $where['is_delete'] = NOT_DELETED;
        $options = [];
        $options['where'] = $where;
        return $this->queryRow($options);
    }

    /**
     * 只查询一次的获取会员职务
     * @param int $titleId
     * @return array
     * User: hjun
     * Date: 2019-01-20 14:55:10
     * Update: 2019-01-20 14:55:10
     * Version: 1.00
     */
    public function getMemberTitleFromCache($titleId = 0)
    {
        static $info;
        if (isset($info)) {
            return $info;
        }
        $info = $this->getMemberTitle($titleId);
        return $info;
    }

    /**
     * 验证会员职务
     * @param int $titleId
     * @return boolean
     * User: hjun
     * Date: 2019-01-20 14:55:27
     * Update: 2019-01-20 14:55:27
     * Version: 1.00
     */
    public function validateMemberTitle($titleId = 0)
    {
        $info = $this->getMemberTitleFromCache($titleId);
        if (empty($info)) {
            $this->setValidateError("当前职务已失效");
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
    public function getFieldsByAction($action = MEMBER_TITLE_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case MEMBER_TITLE_ACTION_ADD:
                $result = ['title_name', 'is_show', 'sort', 'create_time'];
                break;
            case MEMBER_TITLE_ACTION_UPDATE:
                $result = ['title_name', 'is_show', 'sort'];
                break;
            case MEMBER_TITLE_ACTION_DELETE:
                $result = ['is_delete', 'delete_time'];
                break;
            case MEMBER_TITLE_ACTION_CHANGE_SORT:
                $result = ['sort'];
                break;
            case MEMBER_TITLE_ACTION_CHANGE_SHOW:
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
    public function getValidateByAction($action = MEMBER_TITLE_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case MEMBER_TITLE_ACTION_ADD:
                $result = [
                    ['title_name', 'require', "请输入职务名称", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['is_show', '/^[0|1]$/', "请设置职务是否显示", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['sort', 'number', "请设置职务排序", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                ];
                break;
            case MEMBER_TITLE_ACTION_UPDATE:
                $result = [
                    ['title_id', 'validateMemberTitle', "职务已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['title_name', 'require', "请输入职务姓名", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['is_show', '/^[0|1]$/', "请设置职务是否显示", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['sort', 'number', "请设置会员职务排序", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                ];
                break;
            case MEMBER_TITLE_ACTION_DELETE:
                $result = [
                    ['title_id', 'validateMemberTitle', "职务已删除", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                ];
                break;
            case MEMBER_TITLE_ACTION_CHANGE_SORT:
                $result = [
                    ['title_id', 'validateMemberTitle', "职务已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['sort', 'number', "请设置职务排序", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                ];
                break;
            case MEMBER_TITLE_ACTION_CHANGE_SHOW:
                $result = [
                    ['title_id', 'validateMemberTitle', "会员职务已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['is_show', '/^[0|1]$/', "请设置职务是否显示", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
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
    public function getAutoByAction($action = MEMBER_TITLE_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case MEMBER_TITLE_ACTION_ADD:
                $result = [
                    ['create_time', NOW_TIME, self::MODEL_INSERT, 'string'],
                ];
                break;
            case MEMBER_TITLE_ACTION_UPDATE:
                $result = [
                    ['create_time', NOW_TIME, self::MODEL_UPDATE, 'string'],
                ];
                break;
            case MEMBER_TITLE_ACTION_DELETE:
                $result = [
                    ['is_delete', DELETED, self::MODEL_UPDATE, 'string'],
                    ['delete_time', NOW_TIME, self::MODEL_UPDATE, 'string'],
                ];
                break;
            case MEMBER_TITLE_ACTION_CHANGE_SORT:
            case MEMBER_TITLE_ACTION_CHANGE_SHOW:
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
    public function getTypeByAction($action = MEMBER_TITLE_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case MEMBER_TITLE_ACTION_ADD:
                $result = self::MODEL_INSERT;
                break;
            case MEMBER_TITLE_ACTION_UPDATE:
            case MEMBER_TITLE_ACTION_DELETE:
            case MEMBER_TITLE_ACTION_CHANGE_SORT:
            case MEMBER_TITLE_ACTION_CHANGE_SHOW:
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
    public function getDescByAction($action = MEMBER_TITLE_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case MEMBER_TITLE_ACTION_ADD:
                $result = '新增会员职务';
                break;
            case MEMBER_TITLE_ACTION_UPDATE:
                $result = '修改会员职务';
                break;
            case MEMBER_TITLE_ACTION_DELETE:
                $result = '删除会员职务';
                break;
            case MEMBER_TITLE_ACTION_CHANGE_SORT:
                $result = '修改会员职务排序';
                break;
            case MEMBER_TITLE_ACTION_CHANGE_SHOW:
                $result = '修改会员职务是否显示';
                break;
            default:
                $result = '修改会员职务';
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
    public function action($action = MEMBER_TITLE_ACTION_ADD, $request = [])
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
            $data['title_id'] = $this->add($data);
            $results[] = $data['title_id'];
        } elseif ($type == self::MODEL_UPDATE) {
            $data['title_id'] = $request['title_id'];
            $results[] = $this->save($data);
        } elseif ($type == self::MODEL_DELETE) {
            $data['title_id'] = $request['title_id'];
            $results[] = $this->where(['title_id' => $data['title_id']])->delete();
            // 删除后 也要把该职务下的会员都设置为无职务
            $results[] = D('Member')->where(['title_id' => $data['title_id']])->setField('title_id', 0);
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
     * 新增会员职务
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:01
     * Update: 2019-01-20 15:04:01
     * Version: 1.00
     */
    public function addMemberTitle($request = [])
    {
        $result = $this->action(MEMBER_TITLE_ACTION_ADD, $request);
        if (isSuccess($result)) {
            $result['msg'] = "新增成功";
        }
        return $result;
    }

    /**
     * 修改会员职务
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:08
     * Update: 2019-01-20 15:04:08
     * Version: 1.00
     */
    public function updateMemberTitle($request = [])
    {
        $result = $this->action(MEMBER_TITLE_ACTION_UPDATE, $request);
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
        $result = $this->action(MEMBER_TITLE_ACTION_CHANGE_SORT, $request);
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
        $result = $this->action(MEMBER_TITLE_ACTION_CHANGE_SHOW, $request);
        if (isSuccess($result)) {
            $result['msg'] = "修改成功";
        }
        return $result;
    }

    /**
     * 删除会员职务
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:30
     * Update: 2019-01-20 15:04:30
     * Version: 1.00
     */
    public function deleteMemberTitle($request = [])
    {
        $result = $this->action(MEMBER_TITLE_ACTION_DELETE, $request);
        if (isSuccess($result)) {
            $result['msg'] = "删除成功";
        }
        return $result;
    }

    /**
     * 获取选项列表
     * @return array
     * User: hjun
     * Date: 2019-01-20 17:23:28
     * Update: 2019-01-20 17:23:28
     * Version: 1.00
     */
    public function getSelectList()
    {
        $field = ['title_id', 'title_name'];
        $field = implode(',', $field);
        $where = [];
        $where['is_delete'] = NOT_DELETED;
        $order = 'sort ASC,create_time DESC';
        $options = [];
        $options['field'] = $field;
        $options['where'] = $where;
        $options['order'] = $order;
        $list = $this->queryList($options);
        return $list;
    }
}