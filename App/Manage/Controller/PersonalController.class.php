<?php
/**
 * 个人信息操作
 * Class PersonalController
 * @package Manage\Controller
 * User: hjun
 * Date: 2018-03-29 22:56:57
 * Update: 2018-03-29 22:56:57
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
namespace Manage\Controller;

class PersonalController extends CommonController
{

    public function index()
    {

        if (IS_POST) {
            $this->indexPost();
            exit();
        }
        $vo = M('admin')->find(session(C('USER_AUTH_KEY')));

        $this->assign('vo', $vo);
        $this->assign('type', '修改个人信息');
        $this->display();
    }

    //修改
    public function indexPost()
    {

        $email = I('email', '', 'trim');
        $id    = I('uid', 0, 'intval');
        if (empty($email)) {
            $this->error('电子邮箱必须填写！');
        }

        if (M('admin')->where(array('email' => $email, 'id' => array('neq', $id)))->find()) {
            $this->error('失败，邮箱已经存在！');
        }

        $data = array(
            'id'       => $id,
            'email'    => $email,
            'realname' => I('realname', '', 'trim'),
        );
        if (!empty($_POST['password'])) {
            $data['password'] = md5($_POST['password']);
        }

        if (false !== M('admin')->save($data)) {
            $this->success('修改成功', U('Personal/index'));
        } else {

            $this->error('修改失败');
        }

    }

    //修改密码
    public function pwd()
    {
        if (!IS_POST) {
            $this->display();
            exit();
        }

        $id          = session(C('USER_AUTH_KEY'));
        $oldpassword = I('oldpassword', '');
        $password    = I('password', '');
        $rpassword   = I('rpassword', '');
        if (empty($oldpassword)) {
            $this->error('请填写旧密码！');
        }
        if (empty($password)) {
            $this->error('请填写新密码！');
        }

        if ($password != $rpassword) {
            $this->error('两次密码不一样，请重新填写！');
        }

        $self = M('admin')->field(array('email', 'password', 'encrypt'))->where(array('id' => $id))->find();
        if (!$self) {
            $this->error('用户不存在，请重新登录');
        }

        if (get_password($oldpassword, $self['encrypt']) != $self['password']) {
            $this->error('旧密码错误');
        }

        $passwordinfo = get_password($password);

        $data = array(
            'id'       => $id,
            'password' => $passwordinfo['password'],
            'encrypt'  => $passwordinfo['encrypt'],
        );

        if (false !== M('admin')->save($data)) {
            $this->success('修改密码成功', U('Personal/pwd'));
        } else {

            $this->error('修改密码失败');
        }

    }

}
