<?php
/**
 * 后台基本框架页面
 * Class IndexController
 * @package Manage\Controller
 * User: hjun
 * Date: 2018-03-29 22:55:53
 * Update: 2018-03-29 22:55:53
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */

namespace Manage\Controller;

use Common\Lib\Category;
use Common\Model\ContractModel;

class IndexController extends CommonController
{

    public function index()
    {
        /*
         * hjun 2018-03-28 09:57:58 菜单的筛选  园区菜单和后台菜单分开
         */
        $where = [];
        $where['status'] = 1;
        $where['menu_type'] = GARDEN_MENU;
        $menu = M('menu')->where($where)->order('sort,id')->select();
        $menu = empty($menu) ? [] : $menu;
        $where['quick'] = 1;
        $qmenu = M('menu')->where($where)->order('sort,id')->select();
        $qmenu = empty($qmenu) ? [] : $qmenu;
        $menu_c = $qmenu_c = array();

        //权限，是否开启验证且不是超级管理员
        $super = session(C('ADMIN_AUTH_KEY'));
        if (C('USER_AUTH_ON') && empty($super)) {
            if (C('USER_AUTH_TYPE') == 2) {
                //加强验证和即时验证模式
                $accessList = \Org\Util\Rbac::getAccessList(session(C('USER_AUTH_KEY')));
            } else {
                $accessList = session('_ACCESS_LIST');
            }

            foreach ($menu as $k => $v) {
                $v['url'] = '';
                if (empty($v['module']) || empty($v['action'])) {
                    $menu_c[] = $v;
                } elseif (isset($accessList[strtoupper(MODULE_NAME)][strtoupper($v['module'])][strtoupper($v['action'])])) {
                    $v['url'] = U(MODULE_NAME . '/' . $v['module'] . '/' . $v['action']);
                    $menu_c[] = $v;
                }
            }

            foreach ($qmenu as $k => $v) {
                $v['url'] = '';
                if (empty($v['module']) || empty($v['action'])) {
                    $qmenu_c[] = $v;
                } elseif (isset($accessList[strtoupper(MODULE_NAME)][strtoupper($v['module'])][strtoupper($v['action'])])) {
                    $v['url'] = U(MODULE_NAME . '/' . $v['module'] . '/' . $v['action']);
                    $qmenu_c[] = $v;
                }
            }

        } else {

            foreach ($menu as $k => $v) {
                $v['url'] = '';
                if (empty($v['module']) || empty($v['action'])) {
                    $menu_c[] = $v;
                } else {
                    $v['url'] = U(MODULE_NAME . '/' . $v['module'] . '/' . $v['action']);
                    $menu_c[] = $v;
                }
            }

            foreach ($qmenu as $k => $v) {
                $v['url'] = '';
                if (empty($v['module']) || empty($v['action'])) {
                    $qmenu_c[] = $v;
                } else {
                    $v['url'] = U(MODULE_NAME . '/' . $v['module'] . '/' . $v['action']);
                    $qmenu_c[] = $v;
                }
            }

            /*$menu_c  = $menu;
            $qmenu_c = $qmenu;*/
        }

        $menu_list = Category::toLayer($menu_c);
        //子集为空并且链接不存在时删除
        $menu_list = Category::delEmptyParent($menu_list);
        $menu_list = refreshArr($menu_list);
        $menu_json = json_encode($menu_list);

        $this->assign('menu', $menu_list);
        $this->assign('qmenu', $qmenu_c);
        $this->display();
    }

    public function getMenu($pid = 0)
    {
        /*
         * hjun 2018-03-28 10:34:10 后台菜单和园区菜单区分开来
         */
        $where = [];
        $where['status'] = 1;
        $where['menu_type'] = GARDEN_MENU;
        $menu_all = M('menu')->where($where)->order('sort,id')->select();

        $menu_c = $qmenu_c = array();
        $where['quick'] = 1;
        $qmenu = M('menu')->where($where)->order('sort,id')->select();

        $ids = Category::getChildsId($menu_all, $pid, true);

        $where = [];
        $where['menu_type'] = GARDEN_MENU;
        $where['status'] = 1;
        $where['pid'] = array('IN', $ids);
        $menu = M('menu')->where($where)->order('sort,id')->select();

        /*筛选硬件系统判断*/
        foreach ($menu as $k => $val) {
            if ($this->admin['power_switch'] == '0' && $val['module'] == 'PowerMeter') {
                unset($menu[$k]);
            }
            if ($this->admin['parking_switch'] == '0' && $val['module'] == 'Parking') {
                unset($menu[$k]);
            }
            if ($this->admin['doorlock_switch'] == '0' && $val['module'] == 'DoorLock') {
                unset($menu[$k]);
            }
        }
        /*筛选硬件系统判断*/
        foreach ($qmenu as $k2 => $qval) {
            if ($this->admin['power_switch'] == '0' && $qval['module'] == 'PowerMeter') {
                unset($qmenu[$k2]);
            }
            if ($this->admin['parking_switch'] == '0' && $qval['module'] == 'Parking') {
                unset($qmenu[$k2]);
            }
            if ($this->admin['doorlock_switch'] == '0' && $qval['module'] == 'DoorLock') {
                unset($qmenu[$k2]);
            }
        }

        $cate = $catelist = array();
        $cate = $this->getParentCatelist();
        if (!empty($cate['list'])) {
            foreach ($cate['list'] as $key => $val) {

                $val['pid'] = '6';
                $val['id'] = $val['pid'] . '_' . $val['id'];
                $catelist[] = $val;
            }
        }
        //dump($catelist);

        //权限，是否开启验证且不是超级管理员
        $super = session(C('ADMIN_AUTH_KEY'));
        if (C('USER_AUTH_ON') && empty($super)) {
            if (C('USER_AUTH_TYPE') == 2) {
                //加强验证和即时验证模式
                $accessList = \Org\Util\Rbac::getAccessList(session(C('USER_AUTH_KEY')));
            } else {
                $accessList = session('_ACCESS_LIST');
            }

            foreach ($menu as $k => $v) {
                $v['url'] = '';
                if (empty($v['module']) || empty($v['action'])) {
                    $menu_c[] = $v;
                } elseif (isset($accessList[strtoupper(MODULE_NAME)][strtoupper($v['module'])][strtoupper($v['action'])])) {
                    $v['url'] = U(MODULE_NAME . '/' . $v['module'] . '/' . $v['action']);
                    $menu_c[] = $v;
                }
            }
            foreach ($qmenu as $k => $v) {
                $v['url'] = '';
                $v['pid'] = '7';
                if (empty($v['module']) || empty($v['action'])) {
                    $qmenu_c[] = $v;
                } elseif (isset($accessList[strtoupper(MODULE_NAME)][strtoupper($v['module'])][strtoupper($v['action'])])) {
                    $v['url'] = U(MODULE_NAME . '/' . $v['module'] . '/' . $v['action']);
                    $qmenu_c[] = $v;
                }
            }

        } else {

            foreach ($menu as $k => $v) {
                $v['url'] = '';
                if (empty($v['module']) || empty($v['action'])) {
                    $menu_c[] = $v;
                } else {
                    $v['url'] = U(MODULE_NAME . '/' . $v['module'] . '/' . $v['action']);
                    $menu_c[] = $v;
                }
            }

            foreach ($qmenu as $k => $v) {
                $v['url'] = '';
                $v['pid'] = '7';
                if (empty($v['module']) || empty($v['action'])) {
                    $qmenu_c[] = $v;
                } else {
                    $v['url'] = U(MODULE_NAME . '/' . $v['module'] . '/' . $v['action']);
                    $qmenu_c[] = $v;
                }
            }

            /*$menu_c  = $menu;
            $qmenu_c = $qmenu;*/
        }

        if ($pid == 1) {
            $menu_c = array_merge($menu_c, $catelist, $qmenu_c);
        }

        $menu_list = Category::toLayer($menu_c, 'child', $pid);
        //子集为空并且链接不存在时删除
        $menu_list = Category::delEmptyParent($menu_list);
        $menu_list = refreshArr($menu_list);
        $menu_json = json_encode($menu_list);
        exit($menu_json);
    }

    public function getParentCate()
    {
        header("Content-Type:text/html; charset=utf-8"); //不然返回中文乱码
        $count = D('CategoryView')->where(array('pid' => 0, 'type' => 0))->count();
        $list = D('CategoryView')->nofield('content')->where(array('pid' => 0, 'type' => 0))->order('category.sort,category.id')->select();
        if (empty($list)) {
            $list = array();
        }

        //权限检测
        $checkflag = true;
        $super = session(C('ADMIN_AUTH_KEY'));
        if (empty($super)) {
            $checkaccess = M('categoryAccess')->distinct(true)->where(array('flag' => 1, 'roleid' => (int)session('ADMIN.roleid')))->getField('catid', true);

        } else {
            $checkflag = false;
        }
        if (empty($checkaccess)) {
            $checkaccess = array();
        }

        $menudoclist = array('count' => $count);
        foreach ($list as $v) {
            if (!$checkflag || in_array($v['id'], $checkaccess)) {
                $menudoclist['list'][] = array(
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'url' => U(ucfirst($v['tablename']) . '/index', array('pid' => $v['id'])),
                );
            }
        }
        exit(json_encode($menudoclist));
    }

    public function getParentCatelist()
    {
        $count = D('CategoryView')->where(array('pid' => 0, 'type' => 0))->count();
        $list = D('CategoryView')->nofield('content')->where(array('pid' => 0, 'type' => 0))->order('category.sort,category.id')->select();
        if (empty($list)) {
            $list = array();
        }

        //权限检测
        $checkflag = true;
        $super = session(C('ADMIN_AUTH_KEY'));
        if (empty($super)) {
            $checkaccess = M('categoryAccess')->distinct(true)->where(array('flag' => 1, 'roleid' => (int)session('ADMIN.roleid')))->getField('catid', true);

        } else {
            $checkflag = false;
        }
        if (empty($checkaccess)) {
            $checkaccess = array();
        }

        $menudoclist = array('count' => $count);
        foreach ($list as $v) {
            if (!$checkflag || in_array($v['id'], $checkaccess)) {
                $menudoclist['list'][] = array(
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'url' => U(ucfirst($v['tablename']) . '/index', array('pid' => $v['id'])),
                );
            }
        }
        return $menudoclist;
    }


    /**lxq 2018-04-17 start*/

    /**
     * 获取首页的统计数量
     * User: hjun
     * Date: 2018-05-18 16:37:08
     * Update: 2018-05-18 16:37:08
     * Version: 1.00
     */
    public function statistics()
    {
        $roomMeta = D('Room')->getMeta($this->admin['garden_id']);
        $billMeta = D('Bill')->getMeta($this->admin['garden_id']);
        $data = array_merge($roomMeta, $billMeta);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
    }

    /**
     * 获取首页的日历
     * User: hjun
     * Date: 2018-05-18 16:48:25
     * Update: 2018-05-18 16:48:25
     * Version: 1.00
     */
    public function calendar()
    {
        $data = D('Bill')->getCalendarByBills($this->admin['garden_id'], $this->req);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
    }

    /**
     * 获取内容
     * User: hjun
     * Date: 2018-05-20 01:40:18
     * Update: 2018-05-20 01:40:18
     * Version: 1.00
     */
    public function getContent()
    {
        // 数据源
        $view = 'bill';
        if (!empty($this->req['view'])) {
            $view = $this->req['view'];
        }

        $data = [];
        $data1 = D('Contract')->getContent($this->admin['garden_id'], $this->req);
        $data2 = D('Bill')->getContent($this->admin['garden_id'], $this->req);
        switch ($view) {
            case 'contract':
                $data['list'] = $data1;
                break;
            default:
                $data['list'] = $data2;
                break;
        }
        $data['meta'] = [
            'bill_num' => count($data2),
            'contract_num' => count($data1),
        ];
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
    }
}
