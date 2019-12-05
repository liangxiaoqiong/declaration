<?php

namespace Common\Model;

use Common\Interfaces\Action;

/**
 * 会员
 * Class MemberModel.class.php
 * @package Common\Model
 * User: hjun
 * Date: 2018-03-29 23:10:07
 * Update: 2018-03-29 23:10:07
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
class MemberModel extends BaseModel implements Action
{
    protected $tableName = 'member';

    /**
     * 获取会员列表的筛选条件
     * @param array $request
     * @return array
     * User: hjun
     * Date: 2019-01-20 16:31:44
     * Update: 2019-01-20 16:31:44
     * Version: 1.00
     */
    public function getMemberListQueryWhere($request = [])
    {
        $where = [];
        // 会员分类
        if (!empty($request['title_id'])) {
            switch ($request['title_id']) {
                case 'none':
                    $where['a.title_id'] = 0; // 普通会员
                    break;
                default:
                    $where["a.title_id"] = $request['title_id'];
                    break;
            }
        }
        // 会员名称
        if (!empty($request['member_name'])) {
            $where['a.member_name'] = ['LIKE', "%{$request['member_name']}%"];
        }
        // 会员简称
        if (!empty($request['short_name'])) {
            $where['a.short_name'] = ['LIKE', "%{$request['short_name']}%"];
        }
        // 显示状态
        if (!empty($request['is_show'])) {
            $where['a.is_show'] = $request['is_show'] - 1;
        }
        // 推荐状态
        if (!empty($request['is_recommend'])) {
            $where['a.is_recommend'] = $request['is_recommend'] - 1;
        }
        // 有无内容
        if (!empty($request['is_content'])) {
            $where['a.is_content'] = $request['is_content'] - 1;
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
     * 获取会员列表
     * @param int $page
     * @param int $limit
     * @param array $queryWhere
     * @return array
     * User: hjun
     * Date: 2019-01-20 17:10:41
     * Update: 2019-01-20 17:10:41
     * Version: 1.00
     */
    public function getMemberListData($page = DF_PAGE, $limit = DF_PAGE_LIMIT, $queryWhere = [])
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
     * 获取会员信息
     * @param int $memberId
     * @return array
     * User: hjun
     * Date: 2019-01-20 14:53:10
     * Update: 2019-01-20 14:53:10
     * Version: 1.00
     */
    public function getMember($memberId = 0)
    {
        $where = [];
        $where['member_id'] = $memberId;
        $where['is_delete'] = NOT_DELETED;
        $options = [];
        $options['where'] = $where;
        return $this->queryRow($options);
    }

    /**
     * 只查询一次的获取会员
     * @param int $memberId
     * @return array
     * User: hjun
     * Date: 2019-01-20 14:55:10
     * Update: 2019-01-20 14:55:10
     * Version: 1.00
     */
    public function getMemberFromCache($memberId = 0)
    {
        static $info;
        if (isset($info)) {
            return $info;
        }
        $info = $this->getMember($memberId);
        return $info;
    }

    /**
     * 验证会员
     * @param int $memberId
     * @return boolean
     * User: hjun
     * Date: 2019-01-20 14:55:27
     * Update: 2019-01-20 14:55:27
     * Version: 1.00
     */
    public function validateMember($memberId = 0)
    {
        $info = $this->getMemberFromCache($memberId);
        if (empty($info)) {
            $this->setValidateError("会员已失效");
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
    public function getFieldsByAction($action = MEMBER_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case MEMBER_ACTION_ADD:
                $result = [
                    'member_name', 'short_name', 'logo', 'sort', 'is_recommend', 'is_show',
                    'title_id', 'create_time', 'update_time', 'content', 'is_content',
                ];
                break;
            case MEMBER_ACTION_UPDATE:
                $result = [
                    'member_name', 'short_name', 'logo', 'sort', 'is_recommend', 'is_show',
                    'title_id', 'update_time', 'content', 'is_content',
                ];
                break;
            case MEMBER_ACTION_DELETE:
                $result = ['is_delete', 'delete_time'];
                break;
            case MEMBER_ACTION_CHANGE_SORT:
                $result = ['sort'];
                break;
            case MEMBER_ACTION_CHANGE_SHOW:
                $result = ['is_show'];
                break;
            case MEMBER_ACTION_CHANGE_RECOMMEND:
                $result = ['is_recommend'];
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
    public function getValidateByAction($action = MEMBER_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case MEMBER_ACTION_ADD:
                $result = [
                    ['member_name', 'require', "请输入会员名称", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['short_name', 'require', "请输入会员简称", self::VALUE_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['logo', 'url', "请上传会员LOGO", self::VALUE_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['title_id', 'number', "请选择职务", self::VALUE_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['is_recommend', '/^[0|1]$/', "请设置会员是否推荐", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['is_show', '/^[0|1]$/', "请设置会员是否显示", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['sort', 'number', "请设置会员排序", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                ];
                break;
            case MEMBER_ACTION_UPDATE:
                $result = [
                    ['member_id', 'validateMember', "会员已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['member_name', 'require', "请输入会员名称", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['short_name', 'require', "请输入会员简称", self::VALUE_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['logo', 'url', "请上传会员LOGO", self::VALUE_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['title_id', 'number', "请选择职务", self::VALUE_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['is_recommend', '/^[0|1]$/', "请设置会员是否推荐", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['is_show', '/^[0|1]$/', "请设置会员是否显示", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['sort', 'number', "请设置会员排序", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                ];
                break;
            case MEMBER_ACTION_DELETE:
                $result = [
                    ['member_id', 'validateMember', "会员已删除", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                ];
                break;
            case MEMBER_ACTION_CHANGE_SORT:
                $result = [
                    ['member_id', 'validateMember', "会员已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['sort', 'number', "请设置会员排序", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                ];
                break;
            case MEMBER_ACTION_CHANGE_SHOW:
                $result = [
                    ['member_id', 'validateMember', "会员已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['is_show', '/^[0|1]$/', "请设置会员是否显示", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                ];
                break;
            case MEMBER_ACTION_CHANGE_RECOMMEND:
                $result = [
                    ['member_id', 'validateMember', "会员已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['is_recommend', '/^[0|1]$/', "请设置会员是否推荐", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
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
    public function getAutoByAction($action = MEMBER_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case MEMBER_ACTION_ADD:
                $result = [
                    ['create_time', NOW_TIME, self::MODEL_INSERT, 'string'],
                    ['update_time', NOW_TIME, self::MODEL_INSERT, 'string'],
                    ['is_content', empty($request['content']) ? 0 : 1, self::MODEL_INSERT, 'string'],
                ];
                break;
            case MEMBER_ACTION_UPDATE:
                $result = [
                    ['update_time', NOW_TIME, self::MODEL_UPDATE, 'string'],
                    ['is_content', empty($request['content']) ? 0 : 1, self::MODEL_UPDATE, 'string'],
                ];
                break;
            case MEMBER_ACTION_DELETE:
                $result = [
                    ['is_delete', DELETED, self::MODEL_UPDATE, 'string'],
                    ['delete_time', NOW_TIME, self::MODEL_UPDATE, 'string'],
                ];
                break;
            case MEMBER_ACTION_CHANGE_SORT:
            case MEMBER_ACTION_CHANGE_SHOW:
            case MEMBER_ACTION_CHANGE_RECOMMEND:
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
    public function getTypeByAction($action = MEMBER_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case MEMBER_ACTION_ADD:
                $result = self::MODEL_INSERT;
                break;
            case MEMBER_ACTION_UPDATE:
            case MEMBER_ACTION_DELETE:
            case MEMBER_ACTION_CHANGE_SORT:
            case MEMBER_ACTION_CHANGE_SHOW:
            case MEMBER_ACTION_CHANGE_RECOMMEND:
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
    public function getDescByAction($action = MEMBER_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case MEMBER_ACTION_ADD:
                $result = '新增会员';
                break;
            case MEMBER_ACTION_UPDATE:
                $result = '修改会员';
                break;
            case MEMBER_ACTION_DELETE:
                $result = '删除会员';
                break;
            case MEMBER_ACTION_CHANGE_SORT:
                $result = '修改会员排序';
                break;
            case MEMBER_ACTION_CHANGE_SHOW:
                $result = '修改会员是否显示';
                break;
            case MEMBER_ACTION_CHANGE_RECOMMEND:
                $result = '修改会员是否推荐';
                break;
            default:
                $result = '修改会员';
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
    public function action($action = MEMBER_ACTION_ADD, $request = [])
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
            $data['member_id'] = $this->add($data);
            $results[] = $data['member_id'];
        } elseif ($type == self::MODEL_UPDATE) {
            $data['member_id'] = $request['member_id'];
            $results[] = $this->save($data);
        } elseif ($type == self::MODEL_DELETE) {
            $results[] = false;
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
     * 新增会员
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:01
     * Update: 2019-01-20 15:04:01
     * Version: 1.00
     */
    public function addMember($request = [])
    {
        $result = $this->action(MEMBER_ACTION_ADD, $request);
        if (isSuccess($result)) {
            $result['msg'] = "新增成功";
        }
        return $result;
    }

    /**
     * 修改会员
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:08
     * Update: 2019-01-20 15:04:08
     * Version: 1.00
     */
    public function updateMember($request = [])
    {
        $result = $this->action(MEMBER_ACTION_UPDATE, $request);
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
        $result = $this->action(MEMBER_ACTION_CHANGE_SORT, $request);
        if (isSuccess($result)) {
            $result['msg'] = "修改成功";
        }
        return $result;
    }

    /**
     * 改版是否推荐
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:18
     * Update: 2019-01-20 15:04:18
     * Version: 1.00
     */
    public function changeRecommend($request = [])
    {
        $result = $this->action(MEMBER_ACTION_CHANGE_RECOMMEND, $request);
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
        $result = $this->action(MEMBER_ACTION_CHANGE_SHOW, $request);
        if (isSuccess($result)) {
            $result['msg'] = "修改成功";
        }
        return $result;
    }

    /**
     * 删除会员
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:30
     * Update: 2019-01-20 15:04:30
     * Version: 1.00
     */
    public function deleteMember($request = [])
    {
        $result = $this->action(MEMBER_ACTION_DELETE, $request);
        if (isSuccess($result)) {
            $result['msg'] = "删除成功";
        }
        return $result;
    }

    /**
     * 改变文章ID
     * @param array $oldData 旧的文章数据
     * @param array $newData 新的文章数据
     * @return mixed
     * User: hjun
     * Date: 2019-01-20 22:01:48
     * Update: 2019-01-20 22:01:48
     * Version: 1.00
     */
    public function changeArticle($oldData = [], $newData = [])
    {
        $results = [];
        // 如果member_id发生了改变 或者是 删除
        if ($newData['is_delete'] == DELETED || $oldData['member_id'] != $newData['member_id']) {
            // 1. 把原来会员绑定的文章ID设置为0
            if ($oldData['member_id'] > 0) {
                $results[] = $this->where(['member_id' => $oldData['member_id']])->setField('article_id', 0);
            }
            // 2. 设置新会员的文章ID 有设置会员并且没被删除
            if ($newData['is_delete'] == NOT_DELETED && $newData['member_id'] > 0) {
                $results[] = $this->where(['member_id' => $newData['member_id']])->setField('article_id', $oldData['article_id']);
            }

            if (in_array(false, $results, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取会员数据以及职务
     * @param int $memberId
     * @return array
     * User: hjun
     * Date: 2019-01-23 20:20:51
     * Update: 2019-01-23 20:20:51
     * Version: 1.00
     */
    public function getMemberWithTitle($memberId = 0)
    {
        $field = [
            'a.*',
            'b.title_name'
        ];
        $field = implode(',', $field);
        $join = [
            'LEFT JOIN __MEMBER_TITLE__ b ON a.title_id = b.title_id'
        ];
        $where = [];
        $where['a.member_id'] = $memberId;
        $where['a.is_delete'] = NOT_DELETED;
        $options = [];
        $options['alias'] = 'a';
        $options['field'] = $field;
        $options['join'] = $join;
        $options['where'] = $where;
        return $this->queryRow($options);
    }

    /**
     * 获取会员列表
     * @return array
     * User: hjun
     * Date: 2019-01-20 17:10:41
     * Update: 2019-01-20 17:10:41
     * Version: 1.00
     */
    public function getIndexMemberList()
    {
        $field = [
            'a.member_id', 'a.member_name', 'a.member_name', 'a.short_name',
            'a.logo', 'a.title_id', 'a.article_id',
            'b.title_name',
        ];
        $field = implode(',', $field);
        $join = [
            'LEFT JOIN __MEMBER_TITLE__ b ON a.title_id = b.title_id'
        ];
        $where = [];
        $where['a.is_recommend'] = 1;
        $where['a.is_show'] = 1;
        $where['a.is_delete'] = NOT_DELETED;
        $order = 'a.sort ASC,a.create_time DESC';
        $options = [];
        $options['alias'] = 'a';
        $options['field'] = $field;
        $options['join'] = $join;
        $options['where'] = $where;
        $options['order'] = $order;
        $list = $this->queryList($options);
        return $list;
    }

    /**
     * 获取根据职务分类的会员列表
     * @return array
     * User: hjun
     * Date: 2019-01-23 20:07:18
     * Update: 2019-01-23 20:07:18
     * Version: 1.00
     */
    public function getMemberTitleData()
    {
        $model = D('MemberTitle');
        $field = [
            'a.title_id', 'a.title_name',
            'b.member_id', 'b.member_name',
        ];
        $field = implode(',', $field);
        $join = [
            'LEFT JOIN __MEMBER__  b ON a.title_id = b.title_id AND b.is_show = 1 AND b.is_delete = 0'
        ];
        $where = [];
        $where['a.is_show'] = 1;
        $where['a.is_delete'] = NOT_DELETED;
        $order = 'a.sort ASC,b.sort ASC';
        $options = [];
        $options['alias'] = 'a';
        $options['field'] = $field;
        $options['where'] = $where;
        $options['join'] = $join;
        $options['order'] = $order;
        $list = $model->queryList($options);
        if (empty($list)) {
            return [];
        }
        $newList = [];
        foreach ($list as $key => $value) {
            if (!isset($newList[$value['title_id']])) {
                $newList[$value['title_id']] = $value;
                $newList[$value['title_id']]['members'] = [];
            }
            $newList[$value['title_id']]['members'][] = $value;
        }
        return array_values($newList);
    }
}