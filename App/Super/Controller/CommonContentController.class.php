<?php

namespace Super\Controller;

use Think\Controller;

class CommonContentController extends BaseController
{

    //_initialize自动运行方法，在每个方法前，系统会首先运动这个方法
    public function _initialize()
    {

        $user = session(C('USER_AUTH_KEY'));
        if (empty($user)) {
            $this->redirect(MODULE_NAME . '/Login/index');
        }
        C(get_cfg_value()); //添加配置

        $super = session(C('ADMIN_AUTH_KEY'));
        $adminFlag = isset($super) ? $super : 0;
        $adminRole = session('ADMIN.roleid');

        if (!$adminFlag) {
            $pid = I('pid', 0, 'intval');
            if (empty($pid)) {
                $pid = I('get.pid', 0, 'intval');
            }

            check_category_access($pid, ACTION_NAME, $adminRole) || $this->error('没有权限');

        }

    }
}
