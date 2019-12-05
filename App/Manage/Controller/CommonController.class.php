<?php
/**
 * 管理员信息基类
 * Class CommonController
 * @package Manage\Controller
 * User: hjun
 * Date: 2018-03-27 20:22:33
 * Update: 2018-03-27 20:22:33
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */

namespace Manage\Controller;

use Common\Controller\ManageController;

class CommonController extends ManageController
{
    /*
     * 当前管理员信息
     * [
     *   'id' // 管理员ID
     *   'username' // 管理员账号
     *   'nickname' // 管理员昵称
     *   'roleid' // 管理员角色ID
     *   'logintime' // 上次登录时间 格式：2018-03-26 02:49:07
     *   'usertype' // 管理员类型 9-超级管理员 0-普通
     *   'loginip' // 上次登录IP
     *   'loginnum' // 登录次数
     *   'company_id' // 所属园区ID
     * ]
     */
    protected $admin = [];

    //_initialize自动运行方法，在每个方法前，系统会首先运动这个方法
    public function __construct()
    {
        parent::__construct();
        $user = session(C('USER_AUTH_KEY'));
        if (empty($user)) {
            if (IS_AJAX) {
                $this->apiResponse(getReturn(CODE_LOGOUT, '会话已过期,请重新登录', U('Login/index')));
            } else {
                $this->redirect(MODULE_NAME . '/Login/index');
            }
        }

        $this->admin = session('ADMIN');
        if (!empty($this->admin['company_id'])) {
            $company = D('Company')->getCompany($this->admin['company_id']);
            if (!empty($company)) {
                $this->admin = array_merge($this->admin, $company);
            }
        }

        C(get_cfg_value()); //添加配置

        $noAuth = in_array(MODULE_NAME, explode(',', C('NOT_AUTH_MODULE'))) || in_array(ACTION_NAME, explode(',', C('NOT_AUTH_ACTION')));
        //是否开启验证 且 需要验证控制器或方法
        if (C('USER_AUTH_ON') && !$noAuth) {

            //单方文件(非分组)，MODULE_NAME不需要，留空，即RBAC::AccessDecision()
            $result = \Org\Util\Rbac::AccessDecision(MODULE_NAME); //如果没有权限则返回error
            if (false === $result) {
                if (!IS_AJAX) {
                    exit('<h1>没有权限</h1><p><a href="javascript:history.go(-1);">返回</a></p>');
                } else {
                    $this->error('没有权限');
                }
            }

        }

        session('ADMIN', $this->admin);

        $this->assign('ADMIN', $this->admin);
    }
}
