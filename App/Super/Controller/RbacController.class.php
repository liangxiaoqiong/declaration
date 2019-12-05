<?php

namespace Super\Controller;

class RbacController extends CommonController
{

    public function index()
    {

        $keyword = I('keyword', '', 'htmlspecialchars,trim');

        if (empty($keyword)) {
            $where = array('id' => array('GT', '0'));
        } else {
            $where = array('username' => array('like', "%$keyword%"));
        }

        /**
         * 查询自己的员工以及开通的园区账号
         */
        $map = [];
//        $map['garden_admin'] = 1;
        $map['usertype'] = [['eq', SUPER_ADMIN], SUPER_CHILD_ADMIN, 'or'];
        $map['_logic'] = 'or';
        $where['_complex'] = $map;


        //当成一对一来处理
        $count = M('admin')->field('password', true)->where($where)->count();

        $page = new \Common\Lib\PageAdmin($count, 10);
        $page->rollPage = 7;
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow . ',' . $page->listRows;
        $user = M('admin')
            ->field('password', true)
            ->where($where)
            ->limit($limit)->select(); //view
        if ($user) {
            $userTypeName = [
                '0' => '园区后台账号',
                '1' => '总后台员工账号',
                '9' => '总后台账号'
            ];
            foreach ($user as $k => $v) {
                $user[$k]['role'] = D('RoleView')->where(array('user_id' => $v['id']))->select();
                $user[$k]['usertype_name'] = $userTypeName[$v['usertype']];
            }
        }

        /*
        //使用关联模型(多对多),读取除password 字段外 所有字段
        //$user = D('UserRelation')->field('password', true)->relation(true)->select() ;    //relation显示关系表
        //总数
        $count = D('UserRelation')->field('password', true)->relation(true)->where($where)->count();

        $page = new \Common\Lib\Page($count, 10);
        $page->rollPage = 7;
        $page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow. ',' .$page->listRows;
        $user = D('UserRelation')->field('password', true)->relation(true)->where($where)->limit($limit)->select() ;    //relation显示关系表

         */

        $this->assign('user', $user);
        $this->assign('page', $page->show());
        $this->assign('type', '系统用户列表');
        $this->assign('keyword', $keyword);
        $this->display();

    }

    //添加/编辑用户
    public function addUser()
    {

        if (IS_POST) {
            $this->addUserPost();
            exit();
        }

        $uid = I('uid', 0, 'intval');

        $user = M('admin')->find($uid);
        if ($user) {
            $user['password'] = '';
        }
        $userRote = M('role_user')->where(array('user_id' => $uid))->getField('role_id', true);
        if (!is_array($userRote)) {
            $userRote = array(0);
        }

        $where = array();

        /*
         * hjun 2018-03-26 02:59:06
         *
         * 旧代码
         *if ($this->admin['usertype'] == '1' || $this->admin['usertype'] == '9') {
            $where['usertype'] = $this->admin['usertype'];
        } else {
            $where['usertype'] = $this->admin['usertype'];
            $where['garden_id'] = $this->admin['garden_id'];
        }
         * 可选择的角色列表查询条件修改
         *
         * 超级管理员选择自己创建的角色，子账号选择子账号创建的，园区选择园区创建的
         * $where['garden_id'] = $this->admin['garden_id']; // 通过garden_id筛选出园区各自的角色
         * $where['usertype'] = $this->admin['usertype']; // usertype为创建该角色的管理员类型,通过usertype可以防止子账号可以选择超级管理员创建出来的角色
         */
        $where['garden_id'] = $this->admin['garden_id'];
        $where['usertype'] = $this->admin['usertype'];

        $role = M('role')->where($where)->select();

        $this->assign('uid', $uid);
        $this->assign('user', $user);
        $this->assign('userRote', $userRote);
        $this->assign('role', $role);
        $this->display();
    }

    //添加用户处理
    public function addUserPost()
    {

        //M验证
        $validate = array(
            array('username', 'require', '用户名必须填写！'),
            array('username', '', '用户名已经存在！', 0, 'unique', 1),
        );
        $data = M('admin');
        if (!$data->validate($validate)->create()) {
            $this->error($data->getError());
        }

        $passwordinfo = I('password', '', 'get_password');
        $userData = array(
            'username' => I('username', '', 'htmlspecialchars,trim'),
            'password' => $passwordinfo['password'],
            'encrypt' => $passwordinfo['encrypt'],
            'realname' => I('realname', '', 'htmlspecialchars,trim'),
            'logintime' => time(),
            'loginip' => get_client_ip(),
            'islock' => I('islock', 0, 'intval'),
            /*
             * hjun 2018-03-26 03:06:13
             * garden_id 创建该管理员的园区ID
             * usertype 管理员的类型 园区ID大于0则是园区管理员，否则是超级管理员的子账号
             */
            'garden_id' => $this->admin['garden_id'],
            'usertype' => $this->admin['garden_id'] > 0 ? NORMAL_ADMIN : SUPER_CHILD_ADMIN,
        );

        if ($uid = M('admin')->add($userData)) {

            $groupid = I('groupid', 0, 'intval');
            $data = array();
            $data['role_id'] = $groupid;
            $data['user_id'] = $uid;
            M('role_user')->add($data);
            //mysql,支持addAll
            /* if (in_array(strtolower(C('DB_TYPE')), array('mysql', 'mysqli', 'mongo'))) {
                M('role_user')->addAll($role);
            } else {
                foreach ($role as $v) {
                    M('role_user')->add($v);
                }
            } */

            /*  $role = array();
         foreach (I('role_id', array()) as $v) {
             $role[] = array(
                 'user_id' => $uid,
                 'role_id' => $v,
             );
         }
         //mysql,支持addAll
        /*     if (in_array(ser(C('DB_TYPE')), array('mysql', 'mysqli', 'mongo'))) {
             M('role_user')->addAll($role);
         } else {
             foreach ($role as $v) {
                 M('role_user')->add($v);
             }
         }*/

            $this->success('添加成功', U('Rbac/index'));
        } else {

            $this->error('添加失败');
        }

    }

    //修改用户处理
    public function editUser()
    {

        if (!IS_POST) {
            $this->error('参数错误!');
        }
        //M验证
        $password = trim($_POST['password']);
        $username = I('username', '', 'htmlspecialchars,trim');
        $uid = I('uid', 0, 'intval');
        if (empty($username)) {
            $this->error('用户名必须填写！');
        }

        if (M('admin')->where(array('username' => $username, 'id' => array('neq', $uid)))->find()) {
            $this->error('用户名已经存在！');
        }

        $data = array(
            'id' => $uid,
            'username' => $username,
            'realname' => I('realname', '', 'htmlspecialchars,trim'),
            'logintime' => time(),
            'islock' => I('islock', 0, 'intval'),
        );

        //如果密码不为空，即是修改
        if (!$password == '') {
            $passwordinfo = I('password', '', 'get_password');
            $data['password'] = $passwordinfo['password'];
            $data['encrypt'] = $passwordinfo['encrypt'];
        }

        if (false !== M('admin')->save($data)) {

            /*  $role = array();
             foreach (I('role_id', array()) as $v) {
                 $role[] = array(
                     'user_id' => $uid,
                     'role_id' => $v,
                 );
             } */
            $groupid = I('groupid', 0, 'intval');
            M('role_user')->where(array('user_id' => $uid))->delete();
            $data = array();
            $data['role_id'] = $groupid;
            $data['user_id'] = $uid;
            M('role_user')->add($data);
            //mysql,支持addAll
            /* if (in_array(strtolower(C('DB_TYPE')), array('mysql', 'mysqli', 'mongo'))) {
                M('role_user')->addAll($role);
            } else {
                foreach ($role as $v) {
                    M('role_user')->add($v);
                }
            } */
            $this->success('修改成功', U('Rbac/index'));
        } else {

            $this->error('修改失败');
        }

    }

    //删除用户处理
    public function delUser()
    {

        $uid = I('uid', 0, 'intval');
        $batchFlag = I('get.batchFlag', 0, 'intval');
        //批量删除
        if ($batchFlag) {
            $this->delUserAll();
            return;
        }

        if (M('admin')->delete($uid)) {

            M('role_user')->where(array('user_id' => $uid))->delete();
            $this->success('删除成功', U('Rbac/index'));
        } else {

            $this->error('删除失败');
        }

    }

    //指量删除用户处理
    public function delUserAll()
    {

        $idArr = I('key');
        if (isset($idArr) && !is_array($idArr)) {
            $this->error('请选择要删除的列');
        }

        /*

        $errFlag = false;
        $errStr = '';
        foreach ($idArr as $v) {
        if (M('admin')->delete($v)) {
        M('role_user')->where(array('user_id' => $v))->delete();
        }else {
        $errorflag = ture;
        $errStr .= '删除失败ID: '. $v. '<br/>';
        }
        }

        if ($errFlag == ture) {
        $this->error($errStr);
        }else {
        $this->success('删除成功', U('Rbac/index'));
        }
         */

        if (M('admin')->where(array('id' => array('in', $idArr)))->delete()) {
            M('role_user')->where(array('user_id' => array('in', $idArr)))->delete();
            $this->success('删除成功', U('Rbac/index'));
        } else {
            $this->error('删除成功');
        }

    }

    //角色列表
    public function role()
    {

        $keyword = I('keyword', '', 'htmlspecialchars,trim');
        $where = array();

        /*
         * hjun 2018-03-26 02:55:45
         *
         * 旧代码
         *$usertype = $this->admin['usertype'];
        if ($usertype == '1' || $usertype == 9) {
            $where['usertype'] = $usertype;
        } else {
            $where['usertype'] = $usertype;
            $where['garden_id'] = $this->admin['garden_id'];
        }
         * 逻辑修改
         * 超级管理员管理自己创建的角色，超级管理创建的子账号管理子账号创建的角色，园区管理各自园区的角色
         */
//        $where['garden_id'] = $this->admin['garden_id'];
//        $where['usertype'] = $this->admin['usertype'];


        if (!empty($keyword)) {
            $where['name'] = array('like', "%$keyword%");
        }

        //总数
        $count = M('role')->where($where)->count();

        $page = new \Common\Lib\PageAdmin($count, 10);
        $page->rollPage = 7;
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow . ',' . $page->listRows;
        $role = M('role')->where($where)->limit($limit)->select(); //relation显示关系表

        $this->assign('role', $role);
        $this->assign('page', $page->show());
        $this->assign('type', '系统用户组列表');
        $this->assign('keyword', $keyword);
        $this->display();
    }

    //添加角色
    public function addRole()
    {
        if (IS_POST) {
            $this->addRolePost();
            exit();
        }
        $id = I('id', 0, 'intval');
        if (!$id) {
            $type = '添加';
        } else {
            $type = '编辑';
        }
        $role = M('role')->find($id);

        $this->assign('role', $role);
        $this->assign('id', $id);
        $this->assign('type', $type);
        $this->display();
    }

    //添加角色处理
    public function addRolePost()
    {

        //M验证
        $validate = array(
            array('name', 'require', '用户组名必须填写！'),
            array('name', '', '用户组名已经存在！', 0, 'unique', 1),
        );
        $data = M('role');
        if (!$data->validate($validate)->create()) {
            $this->error($data->getError());
        }

        /*
         * hjun 2018-03-26 02:57:54
         *
         * 旧代码
         * if ($this->admin['usertype'] == '1' || $this->admin['usertype'] == '9') {
            $_POST['usertype'] = $this->admin['usertype'];
        } else {
            $_POST['usertype'] = $this->admin['usertype'];
            $_POST['garden_id'] = $this->admin['garden_id'];
        }
         * 逻辑修改
         * garden_id 创建该角色的园区ID
         * usertype 创建该角色的管理员类型
         */
        $_POST['garden_id'] = $this->admin['garden_id'];
        $_POST['usertype'] = $this->admin['usertype'];

        if (M('role')->add($_POST)) {
            $this->success('添加用户组成功', U('Rbac/role'));
        } else {
            $this->error('添加用户组失败');
        }
    }

    //修改角色处理
    public function editRole()
    {

        if (!IS_POST) {
            $this->error('参数错误');
            exit();
        }

        $data = I('post.');
        $id = $data['id'] = I('id', 0, 'intval');
        $name = $data['name'] = trim($data['name']);
        if (empty($name)) {
            $this->error('用户组名必须填写！');
        }

        if (M('role')->where(array('name' => $name, 'id' => array('neq', $id)))->find()) {
            $this->error('用户组已经存在！');
        }

        if (false !== M('role')->save($data)) {
            $this->success('修改用户组成功', U('Rbac/role'));
        } else {
            $this->error('修改用户组失败');
        }
    }

    //删除角色
    public function delRole()
    {
        $id = I('id', 0, 'intval');
        $batchFlag = I('get.batchFlag', 0, 'intval');
        //批量删除
        if ($batchFlag) {
            $this->delRoleAll();
            return;
        }

        if (M('role')->delete($id)) {

            $where = array('role_id' => $id);
            //角色用户中间表
            M('role_user')->where($where)->delete();
            //权限
            M('access')->where($where)->delete();
            $this->success('删除用户组成功', U('Rbac/role'));
        } else {
            $this->error('删除用户组失败');
        }
    }

    //指量删除用户处理
    public function delRoleAll()
    {

        $idArr = I('key');
        if (isset($idArr) && !is_array($idArr)) {
            $this->error('请选择要删除的列');
        }

        if (M('role')->where(array('id' => array('in', $idArr)))->delete()) {
            $where = array('role_id' => array('in', $idArr));
            //角色用户中间表
            M('role_user')->where($where)->delete();
            //权限
            M('access')->where($where)->delete();
            $this->success('删除用户组成功', U('Rbac/role'));
        } else {
            $this->error('删除用户组失败');
        }

    }

    //配置权限
    public function access()
    {
        if (IS_POST) {
            $this->accessPost();
            exit();
        }
        $rid = I('rid', 0, 'intval');
        $access = M('access')->where(array('role_id' => $rid))->getField('node_id', true);

        if (!$access) {
            $access = array();
        }

        /*
         * hjun 2018-03-26 03:18:35 取当前角色ID直接从基类的成员变量中取 不调用session函数 提高性能
         *
         * 分配权限时 除了超级管理员 其他管理员只能基于自己的拥有的权限去分配 则这里要查出当前管理员拥有的权限节点列表
         */
        $role_id = $this->admin['roleid'];
        $access_list = M('access')->where(array('role_id' => $role_id))->getField('node_id', true);
        if ($this->admin['usertype'] != SUPER_ADMIN) {
            if (!empty($access_list)) {
                $access_str = implode(',', $access_list);
                $where['id'] = array('in', $access_str);
            } else {
                $where['id'] = -1;
            }
        }
        $where['status'] = 1;
        $node = M('node')->where($where)->order('sort,id')->select();
        $node = node2layer($node, $access);

        $this->assign('node', $node);
        $this->assign('rid', $rid);
        $this->assign('type', '权限设置');
        $this->display();
    }

    //配置权限处理
    public function accessPost()
    {
        $rid = I('rid', 0, 'intval');
        $access = array();
        //组合权限
        foreach (I('access', array()) as $v) {
            $tmp = explode('_', $v);
            $access[] = array('role_id' => $rid, 'node_id' => $tmp[0], 'level' => $tmp[1]);
        }

        //清空原权限
        M('access')->where(array('role_id' => $rid))->delete();
        if (empty($access)) {
            $this->success('配置成功', U('Rbac/role'));
        }
        //插入新权限
        //mysql,支持addAll
        $ret = 0;
        if (in_array(strtolower(C('DB_TYPE')), array('mysql', 'mysqli', 'mongo'))) {
            $ret = M('access')->addAll($access);
        } else {
            foreach ($access as $v) {
                $ret = M('access')->add($v);
            }
        }

        if ($ret) {
            $this->success('配置成功', U('Rbac/role'));
        } else {
            $this->error('配置失败');
        }

    }

    //节点列表
    public function node()
    {

        $node = M('node')->order('sort')->select();
        $node = node2layer($node);

        $this->assign('node', $node);
        $this->assign('type', '节点列表');
        $this->display();

    }

    //添加节点
    public function addNode()
    {

        if (IS_POST) {
            $this->addNodePost();
            exit();
        }

        $level = I('level', 1, 'intval');
        $pid = I('pid', 0, 'intval');

        $type = '';
        switch ($level) {
            case 1:
                $type = '应用';
                break;
            case 2:
                $type = '控制器';
                break;
            case 3:
                $type = '方法';
                break;
        }

        $this->assign('level', $level);
        $this->assign('pid', $pid);
        $this->assign('type', $type);
        $this->display();
    }

    //添加节点处理
    public function addNodePost()
    {

        $data = I('post.', '');
        $data['name'] = trim($data['name']);
        $data['title'] = trim($data['title']);
        $data['sort'] = I('sort', 0, 'intval');
        $data['status'] = I('status', 0, 'intval');
        if (empty($data['name']) || empty($data['title'])) {
            $this->error('名称和描述不能为空');
        }

        if (M('node')->add($data)) {
            $this->success('添加成功', U('Rbac/node'));
        } else {

            $this->error('添加失败');
        }
    }

    //修改节点
    public function editNode()
    {

        if (IS_POST) {
            $this->editNodePost();
            exit();
        }

        $id = I('id', 0, 'intval');
        $node = M('node')->find($id);
        if (!$node) {
            $this->error('记录不存在');
        }
        switch ($node['level']) {
            case 1:
                $type = '应用';
                break;
            case 2:
                $type = '控制器';
                break;
            case 3:
                $type = '方法';
                break;

        }

        $this->assign('id', $id);
        $this->assign('node', $node);
        $this->assign('type', $type);
        $this->display();
    }

    //修改节点处理
    public function editNodePost()
    {

        $data = I('post.', '');
        $data['name'] = trim($data['name']);
        $data['title'] = trim($data['title']);
        if (empty($data['name']) || empty($data['title'])) {
            $this->error('名称和描述不能为空');
        }

        if (false !== M('node')->save($data)) {
            $this->success('修改成功', U('Rbac/node'));
        } else {

            $this->error('修改失败');
        }

    }

    //删除节点
    public function delNode()
    {

        $id = I('id', 0, 'intval');

        $childNode = M('node')->where(array('pid' => $id))->select();
        if ($childNode) {
            $this->error('删除失败，请先删除下面的子节点');
        }

        if (M('node')->delete($id)) {
            //权限
            M('access')->where(array('node_id' => $id))->delete();
            $this->success('删除成功', U('Rbac/node'));
        } else {

            $this->error('删除失败');
        }
    }

    public function updatestatus()
    {
        $data['id'] = I('id', 0, 'intval');
        $data['islock'] = I('islock', 0, 'intval');
        if ($data['id']) {
            if (false !== M('admin')->save($data)) {
                $this->success('更新成功！', U('index'));
            } else {
                $this->error('更新失败！');
            }
        } else {
            $this->error('参数错误！');
        }
    }

}
