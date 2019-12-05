<?php

namespace Wap\Controller;
class ArticleController extends BaseController
{
    /**
     * 显示文章
     * User: hjun
     * Date: 2019-01-21 23:26:00
     * Update: 2019-01-21 23:26:00
     * Version: 1.00
     */
    public function article()
    {
        $model = D('Article');
        switch ($this->req['action']) {
            case 'get':
                $data = $model->getArt($this->req['article_id']);
                $this->apiResponse(getReturn(CODE_SUCCESS, '', $data));
                break;
            case 'getNavArt':
                $data = $model->getNavArt($this->req['nav_id']);
                $this->apiResponse(getReturn(CODE_SUCCESS, '', $data));
                break;
            case 'queryNavArt':
                $data = $model->getNavArtListData($this->req['nav_id'], $this->req['page'], $this->req['limit']);
                $this->apiResponse(getReturn(CODE_SUCCESS, '', $data));
                break;
            case 'click':
                $data = $model->readArt($this->req);
                $this->apiResponse(getReturn(CODE_SUCCESS, '', $data));
                break;
            default:
                $this->display();
                break;
        }
    }
}