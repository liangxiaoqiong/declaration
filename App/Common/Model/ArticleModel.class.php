<?php

namespace Common\Model;

use Common\Interfaces\Action;

/**
 * 文章
 * Class ArticleModel.class.php
 * @package Common\Model
 * User: hjun
 * Date: 2018-03-29 23:10:07
 * Update: 2018-03-29 23:10:07
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
class ArticleModel extends BaseModel implements Action
{
    protected $tableName = 'article';

    /**
     * 获取文章列表的筛选条件
     * @param array $request
     * @return array
     * User: hjun
     * Date: 2019-01-20 16:31:44
     * Update: 2019-01-20 16:31:44
     * Version: 1.00
     */
    public function getArtListQueryWhere($request = [])
    {
        $where = [];
        // 所属导航
        if (!empty($request['nav_id'])) {
            $where['a.nav_id'] = $request['nav_id'];
        }
        // 文章标题
        if (!empty($request['article_title'])) {
            $where['a.article_title'] = ['LIKE', "%{$request['article_title']}%"];
        }
        // 文章副标题
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
     * 获取文章列表
     * @param int $page
     * @param int $limit
     * @param array $queryWhere
     * @return array
     * User: hjun
     * Date: 2019-01-20 17:10:41
     * Update: 2019-01-20 17:10:41
     * Version: 1.00
     */
    public function getArtListData($page = DF_PAGE, $limit = DF_PAGE_LIMIT, $queryWhere = [])
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
     * 获取文章信息
     * @param int $artId
     * @return array
     * User: hjun
     * Date: 2019-01-20 14:53:10
     * Update: 2019-01-20 14:53:10
     * Version: 1.00
     */
    public function getArt($artId = 0)
    {
        $where = [];
        $where['article_id'] = $artId;
        $where['is_delete'] = NOT_DELETED;
        $options = [];
        $options['where'] = $where;
        return $this->queryRow($options);
    }

    /**
     * 根据导航ID获取文章数据
     * @param int $navId
     * @return array
     * User: hjun
     * Date: 2019-01-23 20:00:20
     * Update: 2019-01-23 20:00:20
     * Version: 1.00
     */
    public function getNavArt($navId = 0)
    {
        $where = [];
        $where['nav_id'] = $navId;
        $where['is_delete'] = NOT_DELETED;
        $options = [];
        $options['where'] = $where;
        return $this->queryRow($options);
    }

    /**
     * 根据导航ID获取文章列表
     * @param int $navId
     * @param int $page
     * @param int $limit
     * @return array
     * User: hjun
     * Date: 2019-01-23 20:02:26
     * Update: 2019-01-23 20:02:26
     * Version: 1.00
     */
    public function getNavArtListData($navId = 0, $page = DF_PAGE, $limit = DF_PAGE_LIMIT)
    {
        $field = [
            'article_id', 'article_title', 'nav_id', 'create_time'
        ];
        $field = implode(',', $field);
        $where = [];
        $where['nav_id'] = $navId;
        $where['is_show'] = 1;
        $where['is_delete'] = NOT_DELETED;
        $order = 'sort ASC,create_time DESC';
        $options = [];
        $options['field'] = $field;
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
     * 只查询一次的获取文章
     * @param int $artId
     * @return array
     * User: hjun
     * Date: 2019-01-20 14:55:10
     * Update: 2019-01-20 14:55:10
     * Version: 1.00
     */
    public function getArtFromCache($artId = 0)
    {
        static $info;
        if (isset($info)) {
            return $info;
        }
        $info = $this->getArt($artId);
        return $info;
    }

    /**
     * 验证文章
     * @param int $artId
     * @return boolean
     * User: hjun
     * Date: 2019-01-20 14:55:27
     * Update: 2019-01-20 14:55:27
     * Version: 1.00
     */
    public function validateArt($artId = 0)
    {
        $info = $this->getArtFromCache($artId);
        if (empty($info)) {
            $this->setValidateError("文章已失效");
            return false;
        }
        return true;
    }

    /**
     * 验证选择的会员
     * @param int $memberId
     * @return boolean
     * User: hjun
     * Date: 2019-01-20 21:56:22
     * Update: 2019-01-20 21:56:22
     * Version: 1.00
     */
    public function validateMemberId($memberId = 0)
    {
        $info = D('Member')->getMemberFromCache($memberId);
        if (empty($info)) {
            $this->setValidateError("选择的会员已失效");
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
                    'article_title', 'nav_id', 'sort', 'article_desc', 'article_img',
                    'content', 'is_show', 'create_time', 'update_time'
                ];
                break;
            case ART_ACTION_UPDATE:
                $result = [
                    'article_title', 'nav_id', 'sort', 'article_desc', 'article_img',
                    'content', 'is_show', 'update_time',
                ];
                break;
            case ART_ACTION_DELETE:
                $result = ['is_delete', 'delete_time'];
                break;
            case ART_ACTION_CHANGE_SORT:
                $result = ['sort'];
                break;
            case ART_ACTION_CHANGE_SHOW:
                $result = ['is_show'];
                break;
            case ART_ACTION_CHANGE_RECOMMEND:
                $result = ['is_recommend'];
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
                    ['article_title', 'require', "请输入文章标题", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['article_img', 'url', "请上传文章封面", self::VALUE_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['content', 'require', "请输入文章内容", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['article_desc', 'require', "请输入文章简述", self::VALUE_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['is_show', '/^[0|1]$/', "请设置文章是否显示", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                    ['sort', 'number', "请设置文章排序", self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
                ];
                break;
            case ART_ACTION_UPDATE:
                $result = [
                    ['article_id', 'validateArt', "文章已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['article_title', 'require', "请输入文章标题", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['article_img', 'url', "请上传文章封面", self::VALUE_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['content', 'require', "请输入文章内容", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['article_desc', 'require', "请输入文章简述", self::VALUE_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['is_show', '/^[0|1]$/', "请设置文章是否显示", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                    ['sort', 'number', "请设置文章排序", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                ];
                break;
            case ART_ACTION_DELETE:
                $result = [
                    ['article_id', 'validateArt', "文章已删除", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                ];
                break;
            case ART_ACTION_CHANGE_SORT:
                $result = [
                    ['article_id', 'validateArt', "文章已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['sort', 'number', "请设置文章排序", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                ];
                break;
            case ART_ACTION_CHANGE_SHOW:
                $result = [
                    ['article_id', 'validateArt', "文章已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['is_show', '/^[0|1]$/', "请设置文章是否显示", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                ];
                break;
            case ART_ACTION_CHANGE_RECOMMEND:
                $result = [
                    ['article_id', 'validateArt', "文章已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
                    ['is_recommend', '/^[0|1]$/', "请设置文章是否推荐", self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
                ];
                break;
            case ART_ACTION_READ:
                $result = [
                    ['article_id', 'validateArt', "文章已失效", self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
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
            case ART_ACTION_DELETE:
                $result = [
                    ['is_delete', DELETED, self::MODEL_UPDATE, 'string'],
                    ['delete_time', NOW_TIME, self::MODEL_UPDATE, 'string'],
                ];
                break;
            case ART_ACTION_CHANGE_SORT:
            case ART_ACTION_CHANGE_SHOW:
            case ART_ACTION_CHANGE_RECOMMEND:
                $result = [];
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
            case ART_ACTION_DELETE:
            case ART_ACTION_CHANGE_SORT:
            case ART_ACTION_CHANGE_SHOW:
            case ART_ACTION_CHANGE_RECOMMEND:
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
                $result = '新增文章';
                break;
            case ART_ACTION_UPDATE:
                $result = '修改文章';
                break;
            case ART_ACTION_DELETE:
                $result = '删除文章';
                break;
            case ART_ACTION_CHANGE_SORT:
                $result = '修改文章排序';
                break;
            case ART_ACTION_CHANGE_SHOW:
                $result = '修改文章是否显示';
                break;
            case ART_ACTION_CHANGE_RECOMMEND:
                $result = '修改文章是否推荐';
                break;
            case ART_ACTION_READ:
                $result = '阅读文章';
                break;
            default:
                $result = '修改文章';
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
            $data['article_id'] = $this->add($data);
            $results[] = $data['article_id'];
        } elseif ($type == self::MODEL_UPDATE) {
            $data['article_id'] = $request['article_id'];
            $results[] = $this->save($data);
        } elseif ($type == self::MODEL_DELETE) {
            $results[] = true;
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
     * 新增文章
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:01
     * Update: 2019-01-20 15:04:01
     * Version: 1.00
     */
    public function addArt($request = [])
    {
        $result = $this->action(ART_ACTION_ADD, $request);
        if (isSuccess($result)) {
            $result['msg'] = "新增成功";
        }
        return $result;
    }

    /**
     * 修改文章
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:08
     * Update: 2019-01-20 15:04:08
     * Version: 1.00
     */
    public function updateArt($request = [])
    {
        $result = $this->action(ART_ACTION_UPDATE, $request);
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
     * 删除文章
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:04:30
     * Update: 2019-01-20 15:04:30
     * Version: 1.00
     */
    public function deleteArt($request = [])
    {
        $result = $this->action(ART_ACTION_DELETE, $request);
        if (isSuccess($result)) {
            $result['msg'] = "删除成功";
        }
        return $result;
    }

    /**
     * 阅读文章
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-20 15:11:15
     * Update: 2019-01-20 15:11:15
     * Version: 1.00
     */
    public function readArt($request = [])
    {
        $result = $this->action(ART_ACTION_READ, $request);
        return $result;
    }
}