<?php
/**
 * 文章管理
 * Class ArtController.class.php
 * @package Manage\Controller
 * User: czx
 * Date: 2019-01-12 20:22:33
 * Update: 2019-01-12 20:22:33
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */

namespace Manage\Controller;

class ArtController extends CommonController
{
    /**
     * 文章列表
     * User: hjun
     * Date: 2019-01-20 16:18:12
     * Update: 2019-01-20 16:18:12
     * Version: 1.00
     */
    public function index()
    {
        switch ($this->req['action']) {
            case 'query':
                $model = D('Article');
                $where = $model->getArtListQueryWhere($this->req);
                $data = $model->getArtListData($this->req['page'], $this->req['limit'], $where);
                $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
                break;
            default:
                $this->display('list');
                exit;
                break;
        }
    }

    /**
     * 添加文章
     * User: hjun
     * Date: 2019-01-20 16:18:31
     * Update: 2019-01-20 16:18:31
     * Version: 1.00
     */
    public function add()
    {
        if (IS_POST) {
            $result = D('Article')->addArt($this->req);
            if (isSuccess($result)) {
                addAdminLog($this->admin, "新增文章,文章ID:{$result['data']['article_id']}");
            }
            $this->apiResponse($result);
        }
    }

    /**
     * 更新文章
     * User: hjun
     * Date: 2019-01-20 16:18:40
     * Update: 2019-01-20 16:18:40
     * Version: 1.00
     */
    public function update()
    {
        $model = D('Article');
        if (IS_GET) {
            switch ($this->req['action']) {
                case 'get':
                    $data = $model->getArt($this->req['article_id']);
                    $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
                    break;
                default:
                    break;
            }
            exit('error');
        }
        if (IS_POST) {
            switch ($this->req['action']) {
                case 'update':
                    $result = $model->updateArt($this->req);
                    $desc = "修改文章,文章ID:{$result['data']['article_id']}";
                    break;
                case 'changeSort':
                    $result = $model->changeSort($this->req);
                    $desc = "修改文章排序,文章ID:{$result['data']['article_id']},排序:{$result['data']['sort']}";
                    break;
                case 'changeRecommend':
                    $result = $model->changeRecommend($this->req);
                    $desc = "修改文章推荐状态,文章ID:{$result['data']['article_id']},推荐:{$result['data']['is_recommend']}";
                    break;
                case 'changeShow':
                    $result = $model->changeShow($this->req);
                    $desc = "修改文章显示状态,文章ID:{$result['data']['article_id']},显示:{$result['data']['is_show']}";
                    break;
                default:
                    break;
            }
            if (isSuccess($result)) {
                addAdminLog($this->admin, $desc);
            }
            $this->apiResponse($result);
        }
    }

    /**
     * 删除文章
     * User: hjun
     * Date: 2019-01-20 16:18:45
     * Update: 2019-01-20 16:18:45
     * Version: 1.00
     */
    public function delete()
    {
        if (IS_POST) {
            $model = D('Article');
            $result = $model->deleteArt($this->req);
            if (isSuccess($result)) {
                addAdminLog($this->admin, "删除文章,文章ID:{$result['data']['article_id']}");
            }
            $this->apiResponse($result);
        }
    }
}
