<?php

namespace Common\Model;

use JsonSchema\Validator;

/**
 * 管理员账号
 * Class BillModel
 * @package Common\Model
 * User: hjun
 * Date: 2018-03-29 23:10:07
 * Update: 2018-03-29 23:10:07
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
class AdminModel extends BaseModel
{
    /**
     * 获取选择的员工列表
     * @param int $gardenId
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2018-04-12 18:56:01
     * Update: 2018-04-12 18:56:01
     * Version: 1.00
     */
    public function getSelectList($gardenId = 0)
    {
        $where = [];
        $where['usertype'] = NORMAL_ADMIN;
        $where['company_id'] = $gardenId;
        $field = ['id', 'username', 'realname', 'company_id'];
        $options = [];
        $options['where'] = $where;
        $options['field'] = $field;
        $list = $this->queryList($options);
        foreach ($list as &$admin) {
            $admin['name'] = empty($admin['realname']) ? $admin['username'] : $admin['realname'];
        }
        return $list;
    }

    /**
     * 获取角色字符串
     * @param int $adminId
     * @return string
     * User: hjun
     * Date: 2018-05-24 18:31:56
     * Update: 2018-05-24 18:31:56
     * Version: 1.00
     */
    public function getRoleStr($adminId = 0)
    {
        $roles = D('RoleView')->where(array('user_id' => $adminId))->select();
        if (empty($roles)) {
            return '无权限';
        }
        $names = [];
        foreach ($roles as $role) {
            $names[] = $role['name'];
        }
        return implode('|', $names);
    }

    /**
     * 获取角色ID
     * @param int $adminId
     * @return int
     * User: hjun
     * Date: 2018-05-24 18:31:56
     * Update: 2018-05-24 18:31:56
     * Version: 1.00
     */
    public function getRole($adminId = 0)
    {
        $id = M('role_user')->where(array('user_id' => $adminId))->getField('role_id');
        return empty($id) ? '' : $id;
    }

    /**
     * 判断是否是园区管理员
     * @param $admin
     * @return boolean
     * User: hjun
     * Date: 2018-05-24 18:33:32
     * Update: 2018-05-24 18:33:32
     * Version: 1.00
     */
    public function isGardenAdmin($admin)
    {
        return $admin['garden_admin'] == 1;
    }

    /**
     * 获取员工列表
     * @param array $admin
     * @param int $page
     * @param int $limit
     * @return array
     * User: hjun
     * Date: 2018-05-24 18:32:06
     * Update: 2018-05-24 18:32:06
     * Version: 1.00
     */
    public function employeeList($admin = [], $page = 1, $limit = 20)
    {
        $where = [];
        $where['company_id'] = $admin['company_id'];
        $where['usertype'] = NORMAL_ADMIN;
        if (!$this->isGardenAdmin($admin)) {
            $where['garden_admin'] = 0;
        }
        $options = [];
        $options['field'] = true;
        $options['where'] = $where;
        $totalList = $this->queryList($options);
        $options['page'] = $page;
        $options['limit'] = $limit;
        $options['order'] = 'garden_admin DESC';
        $list = $this->queryList($options);
        foreach ($list as &$employee) {
            $this->auto([
                ['role_str', 'getRoleStr', self::MODEL_UPDATE, 'callback', [$employee['id']]],
                ['logintime', 'date', self::MODEL_UPDATE, 'function', [DATE_FORMAT . ' H:i:s', $employee['logintime']]],
            ]);
            $this->autoOperation($employee, self::MODEL_UPDATE);
            if ($this->isGardenAdmin($employee)) {
                $employee['role_str'] = '主账号';
            }
            unset($employee['password']);
        }
        return ['list' => $list, 'total' => count($totalList)];
    }

    /**
     * 获取信息
     * @param int $companyId
     * @param int $adminId
     * @return array
     * User: hjun
     * Date: 2018-05-24 18:53:32
     * Update: 2018-05-24 18:53:32
     * Version: 1.00
     */
    public function get($companyId = 0, $adminId = 0)
    {
        $where = [];
        $where['company_id'] = $companyId;
        $where['id'] = $adminId;
        $options = [];
        $options['field'] = true;
        $options['where'] = $where;
        $info = $this->queryRow($options);
        $info['role_id'] = $this->getRole($info['id']);
        $info['password'] = '';
        return $info;
    }

    /**
     * 获取密码
     * @param $password
     * @return string
     * User: hjun
     * Date: 2018-05-25 11:53:44
     * Update: 2018-05-25 11:53:44
     * Version: 1.00
     */
    public function getPassword($password)
    {
        $pwd = get_password($password);
        return $pwd['password'];
    }

    /**
     * 获取加密字符串
     * @param $password
     * @return string
     * User: hjun
     * Date: 2018-05-25 11:53:49
     * Update: 2018-05-25 11:53:49
     * Version: 1.00
     */
    public function getEncrypt($password)
    {
        $pwd = get_password($password);
        return $pwd['password'];
    }

    /**
     * 添加员工
     * @param int $gardenId
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2018-05-25 10:59:55
     * Update: 2018-05-25 10:59:55
     * Version: 1.00
     */
    public function addEmployee($gardenId = 0, $request = [])
    {
        $adminName = '';
        if (empty($request['username'])) return getReturn(CODE_ERROR, '请输入员工账号');
        $request['username'] = "{$adminName}{$request['username']}";
        $fields = ['username', 'password', 'encrypt', 'realname', 'usertype', 'company_id', 'remark'];
        $validate = [
            ['username', 'require', '请输入员工账号', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
            ['username', '/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{1,20}$/', '员工账号需要由字母、数字组成,不能为中文', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
            ['username', '', '账号已经存在！', self::MUST_VALIDATE, 'unique', self::MODEL_INSERT],
            ['password', 'require', '请输入登录密码', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
            ['password', '/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{6,20}$/', '密码由6—20位字母、数字组成', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
            ['realname', 'require', '请输入员工昵称', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
        ];
        $auto = [
            ['company_id', $gardenId, self::MODEL_INSERT, 'string'],
            ['usertype', NORMAL_ADMIN, self::MODEL_INSERT, 'string'],
        ];
        $result = $this->getAndValidateDataFromRequest($fields, $request, $validate, $auto, self::MODEL_INSERT);
        if (!isSuccess($result)) {
            return $result;
        }
        $data = $result['data'];
        $pwd = get_password($request['password']);
        $data['password'] = $pwd['password'];
        $data['encrypt'] = $pwd['encrypt'];

        $this->startTrans();
        $results = [];
        $id = $this->add($data);
        $data['id'] = $id;
        $results[] = $id;

        $roleId = $request['role_id'];
        if (!empty($roleId)) {
            $data = array();
            $data['role_id'] = $roleId;
            $data['user_id'] = $id;
            $results[] = M('role_user')->add($data);
        }

        if (in_array(false, $results, true)) {
            $this->rollback();
            return getReturn(CODE_ERROR);
        }

        $this->commit();
        return getReturn(CODE_SUCCESS, 'success', $data);
    }

    /**
     * 更新员工
     * @param int $gardenId
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2018-05-25 09:45:45
     * Update: 2018-05-25 09:45:45
     * Version: 1.00
     */
    public function update($gardenId = 0, $request = [])
    {
        $info = $this->get($gardenId, $request['id']);
        if (empty($info)) return getReturn(CODE_NOT_FOUND, '账号不存在');

        $fields = ['id', 'realname', 'remark'];
        $data = $this->getAndValidateDataFromRequest($fields, $request, [], [], self::MODEL_UPDATE)['data'];
        //如果密码不为空，即是修改
        if (!empty($request['password'])) {
            $pwd = get_password($request['password']);
            $data['password'] = $pwd['password'];
            $data['encrypt'] = $pwd['encrypt'];
        }


        $this->startTrans();
        $results = [];

        $results[] = $this->save($data);
        // hjun 2018-04-02 01:05:25 主账号不修改角色
        if (!$this->isGardenAdmin($info)) {
            $roleId = $request['role_id'];
            $results[] = M('role_user')->where(array('user_id' => $info['id']))->delete();
            if (!empty($roleId)) {
                $data = array();
                $data['role_id'] = $roleId;
                $data['user_id'] = $info['id'];
                M('role_user')->add($data);
            }
        }
        if (in_array(false, $results, true)) {
            $this->rollback();
            return getReturn(CODE_ERROR);
        }

        $this->commit();
        return getReturn(CODE_SUCCESS, 'success', $data);
    }

    /**
     * 删除员工
     * @param int $gardenId
     * @param int $adminId
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2018-05-25 17:14:40
     * Update: 2018-05-25 17:14:40
     * Version: 1.00
     */
    public function delAdmin($gardenId = 0, $adminId = 0)
    {
        $this->startTrans();
        $results = [];
        $where = [];
        $where['company_id'] = $gardenId;
        $where['id'] = $adminId;
        $results[] = $this->where($where)->delete();
        $results[] = M('role_user')->where(array('user_id' => $adminId))->delete();

        if (in_array(false, $results, true)) {
            $this->rollback();
            return getReturn(CODE_ERROR);
        }
        $this->commit();
        return getReturn(CODE_SUCCESS, 'success');
    }

    public function getSchemaError($error = [])
    {
        $errorTable = [
            'username' => '请输入一个正确的邮箱',
            'password' => '密码以字母开头，长度大于6位，只能包含字符、数字和下划线。',
            'confirm_password' => '密码以字母开头，长度大于6位，只能包含字符、数字和下划线。',
            'nickname' => '昵称长度1-20位',
        ];
        if (isset($errorTable[$error['property']])) {
            return $errorTable[$error['property']];
        }
        return "{$error['property']}:{$error['message']}";
    }

    /**
     * 注册
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-12 16:48:58
     * Update: 2019-01-12 16:48:58
     * Version: 1.00
     */
    public function register($request = [])
    {
        // 检查验证码
        if (!check_verify($request['code'], 'a_register_1')) {
            return getReturn(CODE_ERROR, '验证码错误，请重新输入');
        }

        // 检验数据格式
        $schema = getDefaultData('json/admin/register_schema');
        $validate = new Validator();
        $validate->check($request, $schema);
        if (!$validate->isValid()) {
            $errors = $validate->getErrors();
            $message = $this->getSchemaError($errors[0]);
            return getReturn(CODE_ERROR, $message);
        }

        // 检查确认密码
        if ($request['password'] != $request['confirm_password']) {
            return getReturn(CODE_ERROR, '两次密码输入不一致');
        }

        // 检查用户名是否存在
        $where = [];
        $where['username'] = $request['username'];
        $isExit = $this->field('id')->where($where)->find();
        if ($isExit) {
            return getReturn(CODE_ERROR, '邮箱已经被注册,请选择新邮箱!');
        }

        $pwd = get_password($request['password']);
        $data = [];
        $data['username'] = $request['username'];
        $data['password'] = $pwd['password'];
        $data['encrypt'] = $pwd['encrypt'];
        $data['nickname'] = $request['nickname'];

        $this->startTrans();
        $adminId = $this->add($data);
        if (false === $adminId) {
            $this->rollback();
            return getReturn(CODE_ERROR);
        }

        // 默认设置角色为未认证角色
        $roleId = D('Role')->getUnPassCompanyRoleId();
        if (!empty($roleId)) {
            $data = array();
            $data['role_id'] = $roleId;
            $data['user_id'] = $adminId;
            $results[] = M('role_user')->add($data);
        }

        $this->commit();
        $data['url'] = U('Login/index');
        return getReturn(CODE_SUCCESS, '注册成功！将跳转到登录页！', $data);
    }
}