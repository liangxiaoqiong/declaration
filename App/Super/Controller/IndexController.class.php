<?php

namespace Super\Controller;

use Common\Lib\Category;

class IndexController extends CommonController
{

    public function index()
    {
        /*
         * hjun 2018-03-28 09:57:58 菜单的筛选  园区菜单和后台菜单分开
         */
        $where = [];
        $where['status'] = 1;
        $where['menu_type'] = SUPER_MENU;
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
        $where['menu_type'] = SUPER_MENU;
        $menu_all = M('menu')->where($where)->order('sort,id')->select();

        $menu_c = $qmenu_c = array();
        $where['quick'] = 1;
        $qmenu = M('menu')->where($where)->order('sort,id')->select();

        $ids = Category::getChildsId($menu_all, $pid, true);

        $where = [];
        $where['menu_type'] = SUPER_MENU;
        $where['status'] = 1;
        $where['pid'] = array('IN', $ids);
        $menu = M('menu')->where($where)->order('sort,id')->select();

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

}
