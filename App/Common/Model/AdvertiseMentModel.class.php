<?php

namespace Common\Model;

/**
 * 导航
 * Class AdvertiseMentModel.class.php
 * @package Common\Model
 * User: hjun
 * Date: 2018-03-29 23:10:07
 * Update: 2018-03-29 23:10:07
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
class AdvertiseMentModel extends BaseModel
{
    protected $tableName = 'advertisement';

    /**
     * 获取轮播图列表
     * @param $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: czx
     * Date: 2019-01-20 21:03:14
     * Update: 2019-01-20 21:03:14
     * Version: 1.00
     */
    public function getList()
    {
        $where = array();
        $where['is_delete'] = 0;
        $where['is_show'] = 1;
        $data = $this->where($where)->select();
        return getReturn(200, "成功", $data);
    }

    /**
     * 添加轮播广告
     * @param $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: czx
     * Date: 2019-01-20 21:03:47
     * Update: 2019-01-20 21:03:47
     * Version: 1.00
     */
    public function addAd($request)
    {
        $insertData = array();
        $insertData['ad_img'] = $request['ad_img'];
        $insertData['ad_url'] = $request['ad_url'];
        $insertData['is_show'] = $request['is_show'];
        $insertData['create_time'] =time();
        $tag = $this->add($insertData);
        if ($tag === false) {
            return getReturn(-1, "添加轮播图失败");
        } else {
            return getReturn(200, "添加轮播图成功", $insertData);
        }

    }

    /**
     * 更新轮播图
     * @param $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: czx
     * Date: 2019-01-20 21:04:08
     * Update: 2019-01-20 21:04:08
     * Version: 1.00
     */
    public function updateAd($request)
    {
        $updateData = array();
        $updateData['ad_img'] = $request['ad_img'];
        $updateData['ad_url'] = $request['ad_url'];
        $updateData['is_show'] = $request['is_show'];
        $where = array();
        $where['ad_id'] = $request['ad_id'];
        $tag = $this->where($where)->save($updateData);
        if ($tag === false) {
            return getReturn(-1, "修改轮播图失败");
        } else {
            return getReturn(200, "修改轮播图成功", $updateData);
        }
    }

    /**
     * 删除轮播图
     * @param $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: czx
     * Date: 2019-01-20 21:04:22
     * Update: 2019-01-20 21:04:22
     * Version: 1.00
     */
    public function deleteAd($request)
    {
        $updateData = array();
        $updateData['is_delete'] = 1;
        $updateData['delete_time'] = time();
        $where = array();
        $where['ad_id'] = $request['ad_id'];
        $tag = $this->where($where)->save($updateData);
        if ($tag === false) {
            return getReturn(-1, "删除轮播图失败");
        } else {
            return getReturn(200, "删除轮播图成功", $updateData);
        }
    }

    public function getWapAdData(){
        $where = array();
        $where['is_delete'] = 0;
        $where['is_show'] = 1;
        $data = $this->where($where)->select();
        return getReturn(200, "成功", empty($data) ? [] : $data);
    }
}