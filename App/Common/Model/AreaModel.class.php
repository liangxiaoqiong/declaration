<?php

namespace Common\Model;

/**
 * 行政区划
 * Class AreaModel
 * @package Common\Model
 * User: hjun
 * Date: 2018-04-04 15:19:58
 * Update: 2018-04-04 15:19:58
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
class AreaModel extends BaseModel
{
    /**
     * 判断地区是否存在
     * @param int $regionId
     * @return boolean
     * User: hjun
     * Date: 2018-04-28 10:01:42
     * Update: 2018-04-28 10:01:42
     * Version: 1.00
     */
    public function isRegionExist($regionId = 0)
    {
        $list = $this->getAllList();
        foreach ($list as $region) {
            if ($region['id'] == $regionId) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取所有区域
     * User: hjun
     * Date: 2018-04-04 16:20:06
     * Update: 2018-04-04 16:20:06
     * Version: 1.00
     */
    public function getAllList()
    {
        $options = [];
        $options['field'] = true;
        $options['order'] = 'sort,id';
        $list = $this->queryList($options);
        return $list;
    }

    /**
     * 获取省份列表
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2018-04-04 15:21:41
     * Update: 2018-04-04 15:21:41
     * Version: 1.00
     */
    public function getProvinceList()
    {
        $where = [];
        $where['pid'] = 0;
        $options = [];
        $options['field'] = true;
        $options['where'] = $where;
        $options['order'] = 'sort,id';
        $list = $this->queryList($options);
        return $list;
    }

    /**
     * 获取城市列表
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2018-04-04 15:27:47
     * Update: 2018-04-04 15:27:47
     * Version: 1.00
     */
    public function getCityList()
    {
        $where = [];
        $where['pid'] = 0;
        $pid = $this->where($where)->getField('id', true);
        if (empty($pid)) return [];
        $where = [];
        $where['pid'] = ['in', $pid];
        $options = [];
        $options['field'] = true;
        $options['where'] = $where;
        $options['order'] = 'sort,id';
        $list = $this->queryList($options);
        return $list;
    }

    /**
     * 获取省份的子城市
     * @param int $pid
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2018-04-04 16:14:45
     * Update: 2018-04-04 16:14:45
     * Version: 1.00
     */
    public function getCityListByPid($pid = 0)
    {
        $list = $this->getCityList();
        $child = [];
        foreach ($list as $city) {
            if ($city['pid'] == $pid) {
                $child[] = $city;
            }
        }
        return $child;
    }

    /**
     * 获取区域列表
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2018-04-04 16:11:55
     * Update: 2018-04-04 16:11:55
     * Version: 1.00
     */
    public function getAreaList()
    {
        $where = [];
        $where['pid'] = 0;
        $pid = $this->where($where)->getField('id', true);
        if (empty($pid)) return [];
        $where = [];
        $where['pid'] = ['in', $pid];
        $cid = $this->where($where)->getField('id', true);
        if (empty($cid)) return [];
        $where = [];
        $where['id'] = ['in', $cid];
        $options = [];
        $options['field'] = true;
        $options['where'] = $where;
        $options['order'] = 'sort,id';
        $list = $this->queryList($options);
        return $list;
    }

    /**
     * 获取城市的子区域
     * @param int $pid
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2018-04-04 16:17:45
     * Update: 2018-04-04 16:17:45
     * Version: 1.00
     */
    public function getAreaListByPid($pid = 0)
    {
        $list = $this->getAreaList();
        $child = [];
        foreach ($list as $area) {
            if ($area['pid'] == $pid) {
                $child[] = $area;
            }
        }
        return $child;
    }

    /**
     * 根据ID获取名称
     * @param int $id
     * @return string
     * User: hjun
     * Date: 2018-04-04 16:30:56
     * Update: 2018-04-04 16:30:56
     * Version: 1.00
     */
    public function getNameById($id = 0)
    {
        $list = $this->getAllList();
        foreach ($list as $region) {
            if ($region['id'] == $id) {
                return empty($region['name']) ? '' : $region['name'];
            }
        }
        return '';
    }
}