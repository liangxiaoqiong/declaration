<?php

namespace Common\Model;

use Common\Interfaces\Action;

/**
 * 友情链接
 * Class LinkModel.class.php
 * @package Common\Model
 * User: hjun
 * Date: 2018-03-29 23:10:07
 * Update: 2018-03-29 23:10:07
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
class LinkModel extends BaseModel implements Action
{
    protected $tableName = 'link';

    /**
     * 获取友情链接列表的筛选条件
     * @param array $request
     * @return array
     * User: hjun
     * Date: 2019-01-20 16:31:44
     * Update: 2019-01-20 16:31:44
     * Version: 1.00
     */
    public function getLinkListQueryWhere($request = [])
    {
        $where = [];
        // 链接名称
        if (!empty($request['link_name'])) {
            $where['a.link_name'] = ['LIKE', "%{$request['link_name']}%"];
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
     * 获取友情链接列表
     * @param int $page
     * @param int $limit
     * @param array $queryWhere
     * @return array
     * User: hjun
     * Date: 2019-01-20 17:10:41
     * Update: 2019-01-20 17:10:41
     * Version: 1.00
     */
    public function getLinkListData($page = DF_PAGE, $limit = DF_PAGE_LIMIT, $queryWhere = [])
    {
        $where = [];
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
     * 获取友情链接信息
     * @param int $linkId
     * @return array
     * User: hjun
     * Date: 2019-01-20 14:53:10
     * Update: 2019-01-20 14:53:10
     * Version: 1.00
     */
    public function getLink($linkId = 0)
    {
        $where = [];
        $where['link_id'] = $linkId;
        $options = [];
        $options['where'] = $where;
        return $this->queryRow($options);
    }

    /**
     * 只查询一次的获取友情链接
     * @param int $linkId
     * @return array
     * User: hjun
     * Date: 2019-01-20 14:55:10
     * Update: 2019-01-20 14:55:10
     * Version: 1.00
     */
    public function getLinkFromCache($linkId = 0)
    {
        static $info;
        if (isset($info)) {
            return $info;
        }
        $info = $this->getLink($linkId);
        return $info;
    }

    /**
     * 验证友情链接
     * @param int $linkId
     * @return boolean
     * User: hjun
     * Date: 2019-01-20 14:55:27
     * Update: 2019-01-20 14:55:27
     * Version: 1.00
     */
    public function validateLink($linkId = 0)
    {
        $info = $this->getLinkFromCache($linkId);
        if (empty($info)) {
            $this->setValidateError("当前链接已失效");
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
    public function getFieldsByAction($action = LINK_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case LINK_ACTION_ADD:
                $result = ['link_name', 'link_url', 'sort', 'is_show', 'create_time',];
                break;
            case LINK_ACTION_UPDATE:
                $result = ['link_name', 'link_url', 'sort', 'is_show',];
                break;
            case LINK_ACTION_DELETE:
                $result = [];
                break;
            case LINK_ACTION_CHANGE_SORT:
                $result = ['sort'];
                break;
            case LINK_ACTION_CHANGE_SHOW:
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
    public function getValidateByAction($action = LINK_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case LINK_ACTION_ADD:
                $result = [
                    ['link_name', 'require', "请输入友情链接标题", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['link_url', 'url', "链接必须以http或https开头", self::VALUE_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['is_show', '/^[0|1]$/', "请设置友情链接是否显示", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['sort', 'number', "请设置友情链接排序", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                ];
                break;
            case LINK_ACTION_UPDATE:
                $result = [
                    ['link_id', 'validateLink', "友情链接已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['link_name', 'require', "请输入友情链接标题", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['link_url', 'url', "链接必须以http或https开头", self::VALUE_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['is_show', '/^[0|1]$/', "请设置友情链接是否显示", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['sort', 'number', "请设置友情链接排序", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                ];
                break;
            case LINK_ACTION_DELETE:
                $result = [
                    ['link_id', 'validateLink', "友情链接已删除", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                ];
                break;
            case LINK_ACTION_CHANGE_SORT:
                $result = [
                    ['link_id', 'validateLink', "友情链接已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['sort', 'number', "请设置友情链接排序", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                ];
                break;
            case LINK_ACTION_CHANGE_SHOW:
                $result = [
                    ['link_id', 'validateLink', "友情链接已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['is_show', '/^[0|1]$/', "请设置友情链接是否显示", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
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
    public function getAutoByAction($action = LINK_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case LINK_ACTION_ADD:
                $result = [
                    ['create_time', NOW_TIME, self::MODEL_INSERT, 'string'],
                ];
                break;
            case LINK_ACTION_UPDATE:
            case LINK_ACTION_DELETE:
            case LINK_ACTION_CHANGE_SORT:
            case LINK_ACTION_CHANGE_SHOW:
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
    public function getTypeByAction($action = LINK_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case LINK_ACTION_ADD:
                $result = self::MODEL_INSERT;
                break;
            case LINK_ACTION_UPDATE:
                $result = self::MODEL_UPDATE;
                break;
            case LINK_ACTION_DELETE:
                $result = self::MODEL_DELETE;
                break;
            case LINK_ACTION_CHANGE_SORT:
            case LINK_ACTION_CHANGE_SHOW:
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
    public function getDescByAction($action = LINK_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case LINK_ACTION_ADD:
                $result = '新增友情链接';
                break;
            case LINK_ACTION_UPDATE:
                $result = '修改友情链接';
                break;
            case LINK_ACTION_DELETE:
                $result = '删除友情链接';
                break;
            case LINK_ACTION_CHANGE_SORT:
                $result = '修改友情链接排序';
                break;
            case LINK_ACTION_CHANGE_SHOW:
                $result = '修改友情链接是否显示';
                break;
            default:
                $result = '修改友情链接';
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
    public function action($action = LINK_ACTION_ADD, $request = [])
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
            $data['link_id'] = $this->add($data);
            $results[] = $data['link_id'];
        } elseif ($type == self::MODEL_UPDATE) {
            $data['link_id'] = $request['link_id'];
            $results[] = $this->save($data);
        } elseif ($type == self::MODEL_DELETE) {
            $data['link_id'] = $request['link_id'];
            $results[] = $this->where(['link_id' => $data['link_id']])->delete();
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
     * 新增友情链接
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:01
     * Update: 2019-01-20 15:04:01
     * Version: 1.00
     */
    public function addLink($request = [])
    {
        $result = $this->action(LINK_ACTION_ADD, $request);
        if (isSuccess($result)) {
            $result['msg'] = "新增成功";
        }
        return $result;
    }

    /**
     * 修改友情链接
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:08
     * Update: 2019-01-20 15:04:08
     * Version: 1.00
     */
    public function updateLink($request = [])
    {
        $result = $this->action(LINK_ACTION_UPDATE, $request);
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
        $result = $this->action(LINK_ACTION_CHANGE_SORT, $request);
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
        $result = $this->action(LINK_ACTION_CHANGE_SHOW, $request);
        if (isSuccess($result)) {
            $result['msg'] = "修改成功";
        }
        return $result;
    }

    /**
     * 删除友情链接
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:30
     * Update: 2019-01-20 15:04:30
     * Version: 1.00
     */
    public function deleteLink($request = [])
    {
        $result = $this->action(LINK_ACTION_DELETE, $request);
        if (isSuccess($result)) {
            $result['msg'] = "删除成功";
        }
        return $result;
    }

    /**
     * 获取首页友情链接
     * @return array
     * User: hjun
     * Date: 2019-01-22 23:22:05
     * Update: 2019-01-22 23:22:05
     * Version: 1.00
     */
    public function getIndexLinkList()
    {
        $field = [
            'link_id', 'link_name', 'link_url'
        ];
        $field = implode(',', $field);
        $where = [];
        $where['is_show'] = 1;
        $order = 'sort ASC,link_id ASC';
        $options = [];
        $options['field'] = $field;
        $options['where'] = $where;
        $options['order'] = $order;
        return $this->queryList($options);
    }
}