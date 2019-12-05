<?php

namespace Common\Model;

/**
 * 角色管理
 * Class RoleModel
 * @package Common\Model
 * User: hjun
 * Date: 2018-05-25 14:11:45
 * Update: 2018-05-25 14:11:45
 * Version: 1.00
 * company 启厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
class RoleModel extends BaseModel
{
    /**
     * 角色列表
     * @param int $gardenId
     * @param int $page
     * @param int $limit
     * @return array
     * User: hjun
     * Date: 2018-05-25 14:14:06
     * Update: 2018-05-25 14:14:06
     * Version: 1.00
     */
    public function roleList($gardenId = 0, $page = 1, $limit = 0)
    {
        $where = array();
        $where['garden_id'] = $gardenId;
        $where['usertype'] = NORMAL_ADMIN;
        $options = [];
        $options['where'] = $where;
        $totalList = $this->queryList($options);
        $options['page'] = $page;
        $options['limit'] = $limit;
        $list = $this->queryList($options);
        $data['list'] = $list;
        $data['total'] = count($totalList);
        return $data;
    }

    /**
     * 删除角色
     * @param int $gardenId
     * @param int $roleId
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2018-05-25 17:28:13
     * Update: 2018-05-25 17:28:13
     * Version: 1.00
     */
    public function delRole($gardenId = 0, $roleId = 0)
    {
        $where = [];
        $where['id'] = $roleId;
        $where['garden_id'] = $gardenId;

        $this->startTrans();
        $results = [];
        $results[] = $this->where($where)->delete();

        $where = [];
        $where['role_id'] = $roleId;
        //角色用户中间表
        $results[] = M('role_user')->where($where)->delete();
        //权限
        $results[] = M('access')->where($where)->delete();
        if (in_array(false, $results, true)) {
            $this->rollback();
            return getReturn(CODE_ERROR);
        }
        $this->commit();
        return getReturn(CODE_SUCCESS, 'success');
    }

    /**
     * 添加角色
     * @param int $gardenId
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2018-05-25 17:31:27
     * Update: 2018-05-25 17:31:27
     * Version: 1.00
     */
    public function addRole($gardenId = 0, $request = [])
    {
        $fields = ['name', 'usertype', 'garden_id'];
        $validate = array(
            array('name', 'require', '请输入角色名称', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        );
        $auto = [
            ['usertype', NORMAL_ADMIN, self::MODEL_INSERT, 'string'],
            ['garden_id', $gardenId, self::MODEL_INSERT, 'string']
        ];
        $result = $this->getAndValidateDataFromRequest($fields, $request, $validate, $auto, self::MODEL_INSERT);
        if (!isSuccess($result)) {
            return $result;
        }
        $data = $result['data'];
        $this->startTrans();
        $results = [];
        $results[] = $this->add($data);
        $data['id'] = $results[0];

        $rid = $results[0];
        $access = array();
        //组合权限
        foreach ($request['access'] as $v) {
            $tmp = explode('_', $v);
            $access[] = array('role_id' => $rid, 'node_id' => $tmp[0], 'level' => $tmp[1]);
        }
        //清空原权限
        if (!empty($access)) {
            $results[] = M('access')->addAll($access);
        }

        if (in_array(false, $results, true)) {
            $this->rollback();
            return getReturn(CODE_ERROR);
        }
        $this->commit();
        return getReturn(CODE_SUCCESS, 'success', $data);
    }

    /**
     * 更新角色
     * @param int $gardenId
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2018-05-25 17:41:50
     * Update: 2018-05-25 17:41:50
     * Version: 1.00
     */
    public function updateRole($gardenId = 0, $request = [])
    {
        $fields = ['name'];
        $validate = array(
            array('name', 'require', '请输入角色名称', self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE),
        );
        $result = $this->getAndValidateDataFromRequest($fields, $request, $validate, [], self::MODEL_UPDATE);
        if (!isSuccess($result)) {
            return $result;
        }
        $data = $result['data'];
        $this->startTrans();
        $results = [];
        $results[] = $this->where(['garden_id' => $gardenId, 'id' => $request['id']])->save($data);

        $rid = $request['id'];
        $access = array();
        //组合权限
        foreach ($request['access'] as $v) {
            $tmp = explode('_', $v);
            $access[] = array('role_id' => $rid, 'node_id' => $tmp[0], 'level' => $tmp[1]);
        }
        //清空原权限
        $results[] = M('access')->where(array('role_id' => $rid))->delete();
        if (!empty($access)) {
            $results[] = M('access')->addAll($access);
        }

        if (in_array(false, $results, true)) {
            $this->rollback();
            return getReturn(CODE_ERROR);
        }
        $this->commit();
        return getReturn(CODE_SUCCESS, 'success');
    }

    /**
     * 根据角色名称获取角色ID
     * @param string $name
     * @return int
     * User: hjun
     * Date: 2019-01-12 21:01:17
     * Update: 2019-01-12 21:01:17
     * Version: 1.00
     */
    public function getRoleIdByName($name = '')
    {
        $role = S("{$name}_role");
        if (!empty($role)) {
            return $role['id'];
        }

        $where = [];
        $where['name'] = $name;
        $role = $this->where($where)->find();
        if (empty($role)) {
            return 0;
        }
        S("{$name}_role", $role);
        return $role['id'];
    }

    /**
     * 获取 未认证企业 角色ID
     * @return int
     * User: hjun
     * Date: 2019-01-12 20:57:08
     * Update: 2019-01-12 20:57:08
     * Version: 1.00
     */
    public function getUnPassCompanyRoleId()
    {
        return $this->getRoleIdByName('un_pass_company');
    }

    /**
     * 获取 已认证企业 角色ID
     * @return int
     * User: hjun
     * Date: 2019-01-12 20:57:08
     * Update: 2019-01-12 20:57:08
     * Version: 1.00
     */
    public function getPassCompanyRoleId()
    {
        return $this->getRoleIdByName('pass_company');
    }

    /**
     * 获取 申报管理员 角色ID
     * @return int
     * User: hjun
     * Date: 2019-01-12 20:57:08
     * Update: 2019-01-12 20:57:08
     * Version: 1.00
     */
    public function getCompanyManageRoleId()
    {
        return $this->getRoleIdByName('company_manage');
    }
}