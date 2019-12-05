<?php

namespace Super\Controller;

use Think\Controller;

class CommonController extends BaseController
{
    /*
     * 当前管理员信息
     * [
     *   'id' // 管理员ID
     *   'username' // 管理员账号
     *   'roleid' // 管理员角色ID
     *   'logintime' // 上次登录时间 格式：2018-03-26 02:49:07
     *   'usertype' // 管理员类型 9-超级管理员 0-园区管理员
     *   'loginip' // 上次登录IP
     *   'loginnum' // 登录次数
     *   'garden_id' // 所属园区ID
     *   'garden_admin' // 是否是园区的管理账号
     * ]
     */
    protected $admin = [];

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
        C(get_cfg_value()); //添加配置

        $noAuth = in_array(MODULE_NAME, explode(',', C('NOT_AUTH_MODULE'))) || in_array(ACTION_NAME, explode(',', C('NOT_AUTH_ACTION')));

        //是否开启验证 且 需要验证控制器或方法
        if (C('USER_AUTH_ON') && !$noAuth) {

            //单方文件(非分组)，MODULE_NAME不需要，留空，即RBAC::AccessDecision()
            \Org\Util\Rbac::AccessDecision(MODULE_NAME) || $this->error('没有权限'); //如果没有权限则返回error

        }

        // hjun 2018-03-26 02:45:16 成员变量
        $this->admin = session('ADMIN');
        $this->assign('ADMIN', $this->admin);
    }
}
