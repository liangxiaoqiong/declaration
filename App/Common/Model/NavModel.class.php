<?php

namespace Common\Model;

use Common\Interfaces\Action;

/**
 * 导航
 * Class NavModel.class.php
 * @package Common\Model
 * User: hjun
 * Date: 2018-03-29 23:10:07
 * Update: 2018-03-29 23:10:07
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
class NavModel extends BaseModel implements Action
{
    protected $tableName = 'nav';

    /**
     * 获取导航列表的筛选条件
     * @param array $request
     * @return array
     * User: hjun
     * Date: 2019-01-20 16:31:44
     * Update: 2019-01-20 16:31:44
     * Version: 1.00
     */
    public function getNavListQueryWhere($request = [])
    {
        $where = [];
        // 所属导航
        if (!empty($request['nav_id'])) {
            $where['a.nav_id'] = $request['nav_id'];
        }
        // 导航标题
        if (!empty($request['nav_title'])) {
            $where['a.nav_title'] = ['LIKE', "%{$request['nav_title']}%"];
        }
        // 导航副标题
        if (!empty($request['short_title'])) {
            $where['a.short_title'] = ['LIKE', "%{$request['short_title']}%"];
        }
        // 显示状态
        if (!empty($request['is_show'])) {
            $where['a.is_show'] = $request['is_show'] - 1;
        }
        // 推荐状态
        if (!empty($request['is_recommend'])) {
            $where['a.is_recommend'] = $request['is_recommend'] - 1;
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
     * 获取导航列表
     * @param int $page
     * @param int $limit
     * @param array $queryWhere
     * @return array
     * User: hjun
     * Date: 2019-01-20 17:10:41
     * Update: 2019-01-20 17:10:41
     * Version: 1.00
     */
    public function getNavListData($page = DF_PAGE, $limit = DF_PAGE_LIMIT, $queryWhere = [])
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
     * 获取导航信息
     * @param int $artId
     * @return array
     * User: hjun
     * Date: 2019-01-20 14:53:10
     * Update: 2019-01-20 14:53:10
     * Version: 1.00
     */
    public function getNav($artId = 0)
    {
        $where = [];
        $where['nav_id'] = $artId;
        $where['is_delete'] = NOT_DELETED;
        $options = [];
        $options['where'] = $where;
        return $this->queryRow($options);
    }

    /**
     * 只查询一次的获取导航
     * @param int $artId
     * @return array
     * User: hjun
     * Date: 2019-01-20 14:55:10
     * Update: 2019-01-20 14:55:10
     * Version: 1.00
     */
    public function getNavFromCache($artId = 0)
    {
        static $info;
        if (isset($info)) {
            return $info;
        }
        $info = $this->getNav($artId);
        return $info;
    }

    /**
     * 验证导航
     * @param int $artId
     * @return boolean
     * User: hjun
     * Date: 2019-01-20 14:55:27
     * Update: 2019-01-20 14:55:27
     * Version: 1.00
     */
    public function validateNav($artId = 0)
    {
        $info = $this->getNavFromCache($artId);
        if (empty($info)) {
            $this->setValidateError("导航已失效");
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
    public function getFieldsByAction($action = ART_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case ART_ACTION_ADD:
                $result = [
                    'nav_title', 'nav_id', 'member_id', 'is_recommend', 'sort', 'short_title',
                    'content', 'is_show', 'create_time', 'update_time'
                ];
                break;
            case ART_ACTION_UPDATE:
                $result = [
                    'nav_title', 'nav_id', 'member_id', 'is_recommend', 'sort', 'short_title',
                    'content', 'is_show', 'update_time'
                ];
                break;
            case ART_ACTION_CHANGE_SORT:
                $result = ['sort'];
                break;
            case ART_ACTION_CHANGE_RECOMMEND:
                $result = ['is_recommend'];
                break;
            case ART_ACTION_CHANGE_SHOW:
                $result = ['is_show'];
                break;
            case ART_ACTION_DELETE:
                $result = ['is_delete', 'delete_time'];
                break;
            case ART_ACTION_READ:
                $result = ['read_num'];
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
    public function getValidateByAction($action = ART_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case ART_ACTION_ADD:
                $result = [
                    ['nav_title', 'require', "请输入导航标题", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['short_title', 'require', "请输入导航副标题", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['content', 'require', "请输入导航内容", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['is_recommend', '/^[0|1]$/', "请设置导航是否推荐", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['is_show', '/^[0|1]$/', "请设置导航是否显示", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['sort', 'number', "请设置导航排序", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                ];
                break;
            case ART_ACTION_UPDATE:
                $result = [
                    ['nav_id', 'validateNav', "导航已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['nav_title', 'require', "请输入导航标题", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['short_title', 'require', "请输入导航副标题", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['content', 'require', "请输入导航内容", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['is_recommend', '/^[0|1]$/', "请设置导航是否推荐", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['is_show', '/^[0|1]$/', "请设置导航是否显示", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['sort', 'number', "请设置导航排序", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                ];
                break;
            case ART_ACTION_CHANGE_SORT:
                $result = [
                    ['nav_id', 'validateNav', "导航已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['sort', 'number', "请设置导航排序", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                ];
                break;
            case ART_ACTION_CHANGE_RECOMMEND:
                $result = [
                    ['nav_id', 'validateNav', "导航已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['is_recommend', '/^[0|1]$/', "请设置导航是否推荐", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                ];
                break;
            case ART_ACTION_CHANGE_SHOW:
                $result = [
                    ['nav_id', 'validateNav', "导航已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['is_show', '/^[0|1]$/', "请设置导航是否显示", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                ];
                break;
            case ART_ACTION_DELETE:
                $result = [
                    ['nav_id', 'validateNav', "导航已删除", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                ];
                break;
            case ART_ACTION_READ:
                $result = [
                    ['nav_id', 'validateNav', "导航已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
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
    public function getAutoByAction($action = ART_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case ART_ACTION_ADD:
                $result = [
                    ['create_time', NOW_TIME, self::MODEL_INSERT, 'string'],
                    ['update_time', NOW_TIME, self::MODEL_INSERT, 'string'],
                ];
                break;
            case ART_ACTION_UPDATE:
                $result = [
                    ['update_time', NOW_TIME, self::MODEL_UPDATE, 'string'],
                ];
                break;
            case ART_ACTION_CHANGE_SORT:
                break;
            case ART_ACTION_CHANGE_RECOMMEND:
                break;
            case ART_ACTION_CHANGE_SHOW:
                break;
            case ART_ACTION_DELETE:
                $result = [
                    ['is_delete', DELETED, self::MODEL_UPDATE, 'string'],
                    ['delete_time', NOW_TIME, self::MODEL_UPDATE, 'string'],
                ];
                break;
            case ART_ACTION_READ:
                $result = [
                    ['read_num', ['exp', '`read_num` + 1'], self::MODEL_UPDATE, 'string'],
                ];
                break;
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
    public function getTypeByAction($action = ART_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case ART_ACTION_ADD:
                $result = self::MODEL_INSERT;
                break;
            case ART_ACTION_UPDATE:
            case ART_ACTION_CHANGE_SORT:
            case ART_ACTION_CHANGE_RECOMMEND:
            case ART_ACTION_CHANGE_SHOW:
            case ART_ACTION_DELETE:
            case ART_ACTION_READ:
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
    public function getDescByAction($action = ART_ACTION_ADD, $request = [])
    {
        switch ($action) {
            case ART_ACTION_ADD:
                $result = '新增导航';
                break;
            case ART_ACTION_UPDATE:
                $result = '修改导航';
                break;
            case ART_ACTION_CHANGE_SORT:
                $result = '修改导航排序';
                break;
            case ART_ACTION_CHANGE_RECOMMEND:
                $result = '修改导航是否推荐';
                break;
            case ART_ACTION_CHANGE_SHOW:
                $result = '修改导航是否显示';
                break;
            case ART_ACTION_DELETE:
                $result = '删除导航';
                break;
            case ART_ACTION_READ:
                $result = '阅读导航';
                break;
            default:
                $result = '修改导航';
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
    public function action($action = ART_ACTION_ADD, $request = [])
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
            $data['nav_id'] = $this->add($data);
            $results[] = $data['nav_id'];
        } else {
            $data['nav_id'] = $request['nav_id'];
            $results[] = $this->save($data);
        }
        if (in_array(false, $results, true)) {
            $this->rollback();
            return getReturn(CODE_ERROR);
        }
        $this->commit();
        return getReturn(CODE_SUCCESS, 'success', $data);
    }

    /**
     * 新增导航
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:01
     * Update: 2019-01-20 15:04:01
     * Version: 1.00
     */
    public function addNav($request = [])
    {
        $result = $this->action(ART_ACTION_ADD, $request);
        if (isSuccess($result)) {
            $result['msg'] = "新增成功";
        }
        return $result;
    }

    /**
     * 修改导航
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:08
     * Update: 2019-01-20 15:04:08
     * Version: 1.00
     */
    public function updateNav($request = [])
    {
        $result = $this->action(ART_ACTION_ADD, $request);
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
        $result = $this->action(ART_ACTION_CHANGE_SORT, $request);
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
        $result = $this->action(ART_ACTION_CHANGE_RECOMMEND, $request);
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
        $result = $this->action(ART_ACTION_CHANGE_SHOW, $request);
        if (isSuccess($result)) {
            $result['msg'] = "修改成功";
        }
        return $result;
    }

    /**
     * 删除导航
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:30
     * Update: 2019-01-20 15:04:30
     * Version: 1.00
     */
    public function deleteNav($request = [])
    {
        $result = $this->action(ART_ACTION_DELETE, $request);
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
        $field = ['nav_id', 'pid', 'nav_name'];
        $field = implode(',', $field);
        $where = [];
        $where['is_delete'] = NOT_DELETED;
        $order = 'sort ASC,create_time DESC';
        $options = [];
        $options['field'] = $field;
        $options['where'] = $where;
        $options['order'] = $order;
        $list = $this->queryList($options);
        if (!empty($list)) {
            $other = [['name' => 'nav_name', 'pname' => 'nav_name', 'link' => ' > ']];
            $list = getTreeArr($list, 'nav_id', 'pid', 'child', '0');
            $list = unTree($list);
            $list = getTreeArr($list, 'nav_id', 'pid', 'child', '0', $other);
            $list = unTree($list);
        }
        return $list;
    }

    /**
     * 添加标题栏
     * @param $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: czx
     * Date: 2019-01-20 20:13:00
     * Update: 2019-01-20 20:13:00
     * Version: 1.00
     */
    public function navAdd($request)
    {
        $insertData = array();
        $insertData['nav_name'] = $request['nav_name'];
        $insertData['pid'] = $request['pid'];
        $insertData['nav_type'] = $request['nav_type'];
        $insertData['is_recommend'] = $request['is_recommend'];
        $insertData['url'] = $request['url'];
        $insertData['sort'] = $request['sort'];
        $insertData['is_show'] = $request['is_show'];
        $insertData['advertise_img'] = $request['advertise_img'];
        $insertData['create_time'] = time();
        $tag = $this->add($insertData);
        if ($tag === false) {
            return getReturn(-1, "添加标题栏失败");
        } else {
            return getReturn(200, "添加标题栏成功", $insertData);
        }

    }

    /**
     * 更新导航条
     * @param $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: czx
     * Date: 2019-01-20 21:33:03
     * Update: 2019-01-20 21:33:03
     * Version: 1.00
     */
    public function navUpdate($request)
    {
        $updateData = array();
        $updateData['nav_name'] = $request['nav_name'];
        $updateData['pid'] = $request['pid'];
        $updateData['nav_type'] = $request['nav_type'];
        $updateData['is_recommend'] = $request['is_recommend'];
        $updateData['url'] = $request['url'];
        $updateData['sort'] = $request['sort'];
        $updateData['is_show'] = $request['is_show'];
        $updateData['advertise_img'] = $request['advertise_img'];
        $updateData['update_time'] = time();
        $where = array();
        $where['nav_id'] = $request['nav_id'];
        $tag = $this->where($where)->save($updateData);
        if ($tag === false) {
            return getReturn(-1, "修改标题栏失败");
        } else {
            return getReturn(200, "修改标题栏成功", $updateData);
        }
    }

    /**
     * 删除导航条
     * @param $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: czx
     * Date: 2019-01-20 21:33:19
     * Update: 2019-01-20 21:33:19
     * Version: 1.00
     */
    public function navDelete($request)
    {
        $updateData = array();
        $updateData['is_delete'] = 1;
        $updateData['delete_time'] = time();
        $where = array();
        $where['nav_id'] = $request['nav_id'];
        $tag = $this->where($where)->save($updateData);
        if ($tag === false) {
            return getReturn(-1, "删除标题栏失败");
        } else {
            return getReturn(200, "删除标题栏成功", $updateData);
        }
    }

    /**
     * 获取导航列表
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: czx
     * Date: 2019-01-20 21:33:42
     * Update: 2019-01-20 21:33:42
     * Version: 1.00
     */
    public function getData()
    {
        $where = array();
        $where['is_delete'] = 0;
        $where['pid'] = 0;
        $navParentList = $this->where($where)->order('sort ASC')->select();

        $where = array();
        $where['is_delete'] = 0;
        $where['pid'] = array('gt', 0);
        $navChildList = $this->where($where)->order('sort ASC')->select();
        foreach ($navParentList as $key => $value) {
            foreach ($navChildList as $key2 => $value2) {
                if ($value2['pid'] == $value['nav_id']) {
                    $navParentList[$key]['child'][] = $value2;
                }
            }
        }
        return getReturn(200, "获取导航成功", empty($navParentList) ? [] : $navParentList);
    }

    /**
     * 获取父类的导航
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: czx
     * Date: 2019-01-21 23:13:07
     * Update: 2019-01-21 23:13:07
     * Version: 1.00
     */
    public function getParentNav()
    {
        $where = array();
        $where['is_delete'] = 0;
        $where['pid'] = 0;
        $data = $this->where($where)->select();
        return getReturn(200, "获取父类导航", empty($data) ? [] : $data);
    }

    public function getWapNavData()
    {
        $where = array();
        $where['is_delete'] = 0;
        $where['is_show'] = 1;
        $where['pid'] = 0;
        $navParentList = $this->where($where)->order('sort ASC')->select();

        $where = array();
        $where['is_delete'] = 0;
        $where['is_show'] = 1;
        $where['pid'] = array('gt', 0);
        $navChildList = $this->where($where)->order('sort ASC')->select();
        foreach ($navParentList as $key => $value) {
            foreach ($navChildList as $key2 => $value2) {
                if ($value2['pid'] == $value['nav_id']) {
                    $navParentList[$key]['child'][] = $value2;
                }
            }
        }
        return getReturn(200, '成功', $navParentList);
    }

    /**
     * 将文章根据导航ID分组
     * @param array $articles
     * @return array
     * User: hjun
     * Date: 2019-01-22 22:09:58
     * Update: 2019-01-22 22:09:58
     * Version: 1.00
     */
    public function groupArt($articles = [])
    {
        $result = [];
        foreach ($articles as $article) {
            if (!isset($result[$article['belong_nav_id']])) {
                $result[$article['belong_nav_id']] = $article;
                $result[$article['belong_nav_id']]['articles'] = [];
            }
            if ($article['article_id'] > 0) {
                $result[$article['belong_nav_id']]['articles'][] = $article;
            }
        }
        return array_values($result);
    }

    /**
     * 根据文章列表分出父级导航
     * @param array $articles
     * @return array
     * User: hjun
     * Date: 2019-01-26 15:59:13
     * Update: 2019-01-26 15:59:13
     * Version: 1.00
     */
    public function groupParentNav($articles = [])
    {
        $result = [];
        foreach ($articles as $key => $article) {
            if (!isset($result[$article['pid']])) {
                $result[$article['pid']] = [
                    'nav_id' => $article['pid'],
                    'nav_name' => $article['pname'],
                    'nav_type' => $article['ptype'],
                    'advertise_img' => $article['advertise_img'],
                    'child' => [],
                ];
            }
            $result[$article['pid']]['child'][] = $article;
        }
        foreach ($result as $key => $value) {
            $value['child'] = $this->groupArt($value['child']);
            $result[$key] = $value;
        }
        return array_values($result);
    }

    /**
     * 获取首页推荐的导航以及文章
     * @return array
     * User: hjun
     * Date: 2019-01-22 21:43:34
     * Update: 2019-01-22 21:43:34
     * Version: 1.00
     */
    public function getRecommendNavAndArt()
    {
        $sql = "SELECT d.nav_id pid,
       d.nav_name pname,
       d.nav_type ptype,
       d.advertise_img,
       c.nav_id,
       c.nav_name,
       c.nav_type,
       a.article_id,
       a.article_title,
       a.article_desc,
       a.article_img,
       a.nav_id belong_nav_id,
       a.create_time
FROM imis_article AS a
       JOIN imis_nav c ON a.nav_id = c.nav_id
       JOIN imis_nav d ON c.pid = d.nav_id,
     (SELECT GROUP_CONCAT(article_id order by article_id desc) AS ids
      FROM imis_article
      GROUP BY nav_id) AS b
WHERE a.nav_id > 0
  AND d.is_show = 1
  AND d.is_recommend = 1
  AND d.is_delete = 0
  AND c.is_show = 1
  AND c.is_recommend = 1
  AND c.is_delete = 0
  AND a.is_show = 1
  AND a.is_delete = 0
  AND FIND_IN_SET
  (a.article_id, b.ids)
  BETWEEN
  1
  AND
  5
ORDER BY d.sort ASC
       ,d.nav_id ASC
       ,c.sort ASC
       ,c.nav_id ASC
       ,a.create_time
  DESC
       ,a.nav_id ASC
       , a.article_id DESC;";
        $list = $this->query($sql);
        if (empty($list)) {
            return [];
        }
        $list = $this->groupParentNav($list);
        return $list;
        $field = [
            'a.nav_id', 'a.nav_name', 'a.pid', 'a.advertise_img',
            'b.article_id', 'b.article_title', 'b.nav_id belong_nav_id', 'b.create_time',
            'b.article_img',
        ];
        $field = implode(',', $field);
        $where = [];
        $where['a.is_recommend'] = 1;
        $where['a.is_show'] = 1;
        $where['a.is_delete'] = NOT_DELETED;
        $join = [
            'LEFT JOIN __ARTICLE__ b ON a.nav_id = b.nav_id AND a.pid > 0 AND b.is_recommend = 1 AND b.is_show = 1 AND b.is_delete = 0'
        ];
        $order = 'a.sort ASC,a.nav_id ASC,b.sort ASC,b.create_time DESC';
        $options = [];
        $options['alias'] = 'a';
        $options['field'] = $field;
        $options['join'] = $join;
        $options['where'] = $where;
        $options['order'] = $order;
        $list = $this->queryList($options);
        if (empty($list)) {
            return [];
        }
        $list = getTreeArr($list, 'nav_id', 'pid', 'child', 0);
        $list = array_values($list);
        foreach ($list as $key => $value) {
            $result = $this->groupArt($value['child']);
            $value['child'] = $result;
            $list[$key] = $value;
        }
        return $list;
    }
}