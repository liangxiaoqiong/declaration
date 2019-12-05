<?php

namespace Manage\Controller;
/**
 * 员工管理
 * Class  RbacController
 * @package Manage\Controller
 * User: hjun
 * Date: 2018-03-29 22:57:43
 * Update: 2018-03-29 22:57:43
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
class RbacController extends CommonController
{

    /*lxq 2018-04-20 员工列表*/
    public function index()
    {
        if (IS_POST) {
            $page = $this->req['page'] > 0 ? $this->req['page'] : 1;
            $limit = $this->req['limit'] > 0 ? $this->req['limit'] : 20;
            $data = D('Admin')->employeeList($this->admin, $page, $limit);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        } else {
            $this->display('staffList');
        }
    }

    /**
     * 获取员工信息
     * User: hjun
     * Date: 2018-05-25 17:03:35
     * Update: 2018-05-25 17:03:35
     * Version: 1.00
     */
    public function info()
    {
        $info = D('Admin')->get($this->admin['garden_id'], $this->req['id']);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $info));
    }

    /**
     * 添加员工
     * User: hjun
     * Date: 2018-05-25 12:57:46
     * Update: 2018-05-25 12:57:46
     * Version: 1.00
     */
    public function addUserPost()
    {
        if (IS_POST) {
            $result = D('Admin')->addEmployee($this->admin['garden_id'], $this->req);
            if (isSuccess($result)) {
                $result['msg'] = '添加成功';
                addAdminLog($this->admin, "添加员工,员工ID:{$result['data']['id']}");
            }
            $this->apiResponse($result);
        }
        exit('error');
    }

    /**
     * 修改员工
     * User: hjun
     * Date: 2018-05-25 09:41:39
     * Update: 2018-05-25 09:41:39
     * Version: 1.00
     */
    public function editUser()
    {
        if (IS_POST) {
            $result = D('Admin')->update($this->admin['garden_id'], $this->req);
            if (isSuccess($result)) {
                $result['msg'] = '修改成功';
                addAdminLog($this->admin, "修改员工,ID:{$this->req['id']}");
            }
            $this->apiResponse($result);
        }
        exit('error');
    }

    /**
     * 删除员工
     * User: hjun
     * Date: 2018-05-25 17:16:04
     * Update: 2018-05-25 17:16:04
     * Version: 1.00
     */
    public function delUser()
    {
        if (IS_POST) {
            $result = D('Admin')->delAdmin($this->admin['garden_id'], $this->req['id']);
            if (isSuccess($result)) {
                $result['msg'] = '删除成功';
                addAdminLog($this->admin, "删除员工,ID:{$this->req['id']}");
            }
            $this->apiResponse($result);
        }
        exit('error');
    }

    /**
     * 获取角色列表
     * User: hjun
     * Date: 2018-05-25 17:22:06
     * Update: 2018-05-25 17:22:06
     * Version: 1.00
     */
    public function role()
    {
        if (IS_POST) {
            $page = $this->req['page'] > 0 ? $this->req['page'] : 1;
            $limit = $this->req['limit'] > 0 ? $this->req['limit'] : 20;
            $data = D('Role')->roleList($this->admin['garden_id'], $page, $limit);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        } else {
            $this->display('roleList');
        }
    }

    /**
     * 删除角色
     * User: hjun
     * Date: 2018-05-25 17:24:25
     * Update: 2018-05-25 17:24:25
     * Version: 1.00
     */
    public function delRole()
    {
        if (IS_POST) {
            $result = D('Role')->delRole($this->admin['garden_id'], $this->req['id']);
            if (isSuccess($result)) {
                $result['msg'] = '删除成功';
                addAdminLog($this->admin, "删除角色,角色ID,{$this->req['id']}");
            }
            $this->apiResponse($result);

        }
        exit('error');
    }

    /**
     * 添加角色
     * User: hjun
     * Date: 2018-05-25 17:31:03
     * Update: 2018-05-25 17:31:03
     * Version: 1.00
     */
    public function addRole()
    {
        if (IS_POST) {
            $result = D('Role')->addRole($this->admin['garden_id'], $this->req);
            if (isSuccess($result)) {
                $result['msg'] = '添加成功';
                addAdminLog($this->admin, "添加角色,角色ID:{$result['data']['id']}");
            }
            $this->apiResponse($result);
        }
        exit('error');
    }

    /**
     * 修改角色
     * User: hjun
     * Date: 2018-05-25 17:44:36
     * Update: 2018-05-25 17:44:36
     * Version: 1.00
     */
    public function editRole()
    {
        if (IS_POST) {
            $result = D('Role')->updateRole($this->admin['garden_id'], $this->req);
            if (isSuccess($result)) {
                $result['msg'] = '修改成功';
                addAdminLog($this->admin, "修改角色,角色ID:{$this->req['id']}");
            }
            $this->apiResponse($result);
        }
        exit('error');
    }

    /**
     * 操作日志
     * User: hjun
     * Date: 2018-05-25 17:58:46
     * Update: 2018-05-25 17:58:46
     * Version: 1.00
     */
    public function logList()
    {
        if (IS_POST) {
            $where = [];
            $page = $this->req['page'] > 0 ? $this->req['page'] : 1;
            $limit = $this->req['limit'] > 0 ? $this->req['limit'] : 20;
            // 操作时间
            if (!empty($this->req['time_min']) || !empty($this->req['time_max'])){
                $this->req['time_min'] = strtotime($this->req['time_min']);
                $this->req['time_max'] = strtotime($this->req['time_max']);
                $result = getRangeWhere($this->req, 'time_min', 'time_max', '开始时间不能大于结束时间');
                if (!isSuccess($result)) {
                    $this->apiResponse($result);
                }
                if (!empty($result['data'])) {
                    $where['create_time'] = $result['data'];
                }
            }


            // 操作者
            if (!empty($this->req['name'])) {
                $where['admin_name'] = ['LIKE', "%{$this->req['name']}%"];
            }

            $where['admin_type'] = $this->admin['usertype'];
            if ($this->admin['usertype'] == NORMAL_ADMIN) {
                $where['company_id'] = $this->admin['company_id'];
            }

            $data = D('AdminLog')->logList($page, $limit, $where);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        } else {
            $this->display();
        }
    }

    public function index_old()
    {

        $keyword = I('keyword', '', 'htmlspecialchars,trim');

        if (empty($keyword)) {
            $where = array('id' => array('GT', '0'));
        } else {
            $where = array('username' => array('like', "%$keyword%"));
        }

        // 这里只能查看园区管理员 不是主账号不能看到主账号
        $where['garden_id'] = $this->admin['garden_id'];
        $where['usertype'] = NORMAL_ADMIN;
        if ($this->admin['garden_admin'] != 1) {
            $where['garden_admin'] = 0;
        }

        //当成一对一来处理
        $count = M('admin')->field('password', true)->where($where)->count();

        $page = new \Common\Lib\PageAdmin($count, 10);
        $page->rollPage = 7;
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow . ',' . $page->listRows;
        $user = M('admin')->field('password', true)->where($where)->limit($limit)->select(); //view
        if ($user) {
            foreach ($user as $k => $v) {
                $user[$k]['role'] = D('RoleView')->where(array('user_id' => $v['id']))->select();
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
        $this->assign('type', '员工列表');
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

    //添加角色处理
    public function addRolePost()
    {

        //M验证
        $validate = array(
            array('name', 'require', '用户组名必须填写！'),
//            array('name', '', '用户组名已经存在！', 0, 'unique', 1),
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
        $_POST['usertype'] = NORMAL_ADMIN;

        if (M('role')->add($_POST)) {
            $this->success('添加成功', U('Rbac/role'));
        } else {
            $this->error('添加失败');
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
