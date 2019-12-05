<?php

namespace Common\Model;

use Common\Interfaces\Action;

/**
 * 企业
 * Class CompanyModel.class.php
 * @package Common\Model
 * User: hjun
 * Date: 2018-03-29 23:10:07
 * Update: 2018-03-29 23:10:07
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
class CompanyModel extends BaseModel implements Action
{
    /**
     * 获取企业信息
     * @param int $companyId
     * @return array
     * User: hjun
     * Date: 2019-01-12 20:22:21
     * Update: 2019-01-12 20:22:21
     * Version: 1.00
     */
    public function getCompany($companyId = 0)
    {
        if (empty($companyId)) {
            return [];
        }
        $info = S("company_{$companyId}");
        if (!empty($info)) {
            return $info;
        }

        $where = [];
        $where['company_id'] = $companyId;
        $where['is_delete'] = NOT_DELETED;
        $info = $this->where($where)->find();
        if (empty($info)) {
            return [];
        }

        S("company_{$companyId}", $info);
        return $info;
    }

    /**
     * 检查能否修改资料
     * @return boolean
     * User: hjun
     * Date: 2019-01-13 18:38:35
     * Update: 2019-01-13 18:38:35
     * Version: 1.00
     */
    public function validateCanSaveInfo()
    {
        // 还未填写资料的可以
        if (!$this->validateHasCompany()) {
            return true;
        }
        $admin = $this->getAdminFromCache();
        $company = $this->getCompanyFromCache($admin['company_id']);
        // 已经通过认证了 可以修改资料
        if ($this->isAuth($company)) {
            return true;
        }
        // 审核中不能修改
        if ($this->isAuditing($company)) {
            $this->setValidateError("认证审核中,无法修改资料");
            return false;
        }
        return true;
    }

    /**
     * 验证能否提交申请
     * @return boolean
     * User: hjun
     * Date: 2019-01-13 17:05:36
     * Update: 2019-01-13 17:05:36
     * Version: 1.00
     */
    public function validateCanCompanyApply()
    {
        // 还未填写资料的可以直接提交申请
        if (!$this->validateHasCompany()) {
            return true;
        }
        $admin = $this->getAdminFromCache();
        $company = $this->getCompanyFromCache($admin['company_id']);
        // 已经通过认证了 就不能再提交了
        if ($this->isAuth($company)) {
            $this->setValidateError("您已通过认证,无法提交");
            return false;
        }
        // 审核中也不能提交
        if ($this->isAuditing($company)) {
            $this->setValidateError("认证审核中,无法提交");
            return false;
        }
        return true;
    }

    /**
     * 判断能否进行通过认证操作
     * @param int $companyId
     * @return boolean
     * User: hjun
     * Date: 2019-01-13 17:19:08
     * Update: 2019-01-13 17:19:08
     * Version: 1.00
     */
    public function validateCanPassApply($companyId = 0)
    {
        $company = $this->getCompanyFromCache($companyId);
        if (empty($company)) {
            $this->setValidateError("该企业已失效");
            return false;
        }
        if ($this->isAuth($company)) {
            $this->setValidateError("该企业已通过认证");
            return false;
        }
        if (!$this->isCompanyApply($company)) {
            $this->setValidateError("该企业未提交申请,无法进行通过操作");
            return false;
        }
        if ($this->isRefuseAudit($company)) {
            $this->setValidateError("该企业已被拒绝,请等待其再次提交后才能进行通过操作");
            return false;
        }
        return true;
    }

    /**
     * 判断能否进行拒绝认证操作
     * @param int $companyId
     * @return boolean
     * User: hjun
     * Date: 2019-01-13 17:19:08
     * Update: 2019-01-13 17:19:08
     * Version: 1.00
     */
    public function validateCanRefuseApply($companyId = 0)
    {
        $company = $this->getCompanyFromCache($companyId);
        if (empty($company)) {
            $this->setValidateError("该企业已失效");
            return false;
        }
        if ($this->isAuth($company)) {
            $this->setValidateError("该企业已通过认证,无法进行拒绝操作");
            return false;
        }
        if (!$this->isCompanyApply($company)) {
            $this->setValidateError("该企业未提交申请,无法进行拒绝操作");
            return false;
        }
        if ($this->isRefuseAudit($company)) {
            $this->setValidateError("该企业已被拒绝,无需重复操作");
            return false;
        }
        return true;
    }

    /**
     * 完成快照
     * @param int $companyId
     * @return string
     * User: hjun
     * Date: 2019-01-13 15:29:16
     * Update: 2019-01-13 15:29:16
     * Version: 1.00
     */
    public function autoPassSnapshot($companyId = 0)
    {
        $info = $this->getCompanyFromCache($companyId);
        return jsonEncode($info);
    }

    /**
     * 获取操作字段
     * @param int $action
     * @param array $request
     * @return array
     * User: hjun
     * Date: 2019-01-13 13:33:25
     * Update: 2019-01-13 13:33:25
     * Version: 1.00
     */
    public function getFieldsByAction($action = COMPANY_ACTION_ADD, $request = [])
    {
        $fieldsTable = [];
        $fieldsTable[COMPANY_ACTION_ADD] = [
            'company_name', 'company_form', 'area_id_1', 'area_id_2', 'area_id_3',
            'is_high_new', 'capital_form', 'license_no', 'address', 'capital',
            'establishment_time', 'legal_person', 'lp_mobile', 'tel', 'email',
            'introduction', 'license_image', 'apply_fields', 'scope', 'contact_name',
            'contact_duty', 'contact_mobile', 'contact_tel', 'contact_fax', 'contact_email',
            'contact_qq', 'contact_wechat', 'create_time', 'save_time'
        ];
        $fieldsTable[COMPANY_ACTION_SAVE] = [
            'company_name', 'company_form', 'area_id_1', 'area_id_2', 'area_id_3',
            'is_high_new', 'capital_form', 'license_no', 'address', 'capital',
            'establishment_time', 'legal_person', 'lp_mobile', 'tel', 'email',
            'introduction', 'license_image', 'apply_fields', 'scope', 'contact_name',
            'contact_duty', 'contact_mobile', 'contact_tel', 'contact_fax', 'contact_email',
            'contact_qq', 'contact_wechat', 'save_time',
        ];
        $fieldsTable[COMPANY_ACTION_APPLY] = [
            'company_name', 'company_form', 'area_id_1', 'area_id_2', 'area_id_3',
            'is_high_new', 'capital_form', 'license_no', 'address', 'capital',
            'establishment_time', 'legal_person', 'lp_mobile', 'tel', 'email',
            'introduction', 'license_image', 'apply_fields', 'scope', 'contact_name',
            'contact_duty', 'contact_mobile', 'contact_tel', 'contact_fax', 'contact_email',
            'contact_qq', 'contact_wechat', 'company_apply_status', 'company_apply_time', 'audit_status'
        ];
        // 如果是认证申请 如果还未有企业数据 则需要增加一些字段
        if ($action === COMPANY_ACTION_APPLY && !$this->validateHasCompany()) {
            $fieldsTable[COMPANY_ACTION_APPLY] = array_merge($fieldsTable[COMPANY_ACTION_APPLY], [
                'create_time', 'save_time'
            ]);
        }
        $fieldsTable[COMPANY_ACTION_PASS_APPLY] = [
            'auth_status', 'auth_time', 'audit_status', 'audit_time', 'pass_snapshot'
        ];
        $fieldsTable[COMPANY_ACTION_REFUSE_APPLY] = [
            'audit_status', 'audit_time', 'audit_reason'
        ];
        return isset($fieldsTable[$action]) ? $fieldsTable[$action] : [];
    }

    /**
     * 根据操作获取验证规则
     * @param int $action
     * @param array $request
     * @return array
     * User: hjun
     * Date: 2019-01-13 14:55:28
     * Update: 2019-01-13 14:55:28
     * Version: 1.00
     */
    public function getValidateByAction($action = COMPANY_ACTION_ADD, $request = [])
    {
        $validateTable = [];
        // 新增
        $validateTable[COMPANY_ACTION_ADD] = [
            ['company_name', 'require', '请输入公司名称', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['company_form', 'number', '请选择公司性质', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['area_id_1', 'number', '请选择省', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['area_id_2', 'number', '请选择市', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['area_id_3', 'number', '请选择区', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['is_high_new', '/^[0|1]$/', '请选择是否是高新企业', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['capital_form', 'number', '请选择所有制形式', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['license_no', 'require', '请输入统一社会信用代码', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['address', 'require', '请输入注册地址', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['capital', 'number', '请输入注册资本（万元）', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['establishment_time', 'validateTime', '请选择成立时间', self::MUST_VALIDATE, 'function', self::MODEL_BOTH],
            ['legal_person', 'require', '请输入法人代表', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['lp_mobile', '/^1[3|4|5|8|9][0-9]\d{8}$/', '请输入法人手机', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['tel', '/^\d{3,4}-?\d{7,8}$/', '请输入企业电话', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['email', 'email', '请输入企业邮箱', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['introduction', 'require', '请输入公司简介', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['license_image', 'url', '请上传营业执照', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['apply_fields', '/^(\d+,)*\d+$/', '请选择应用领域', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['scope', 'require', '请输入经营范围', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['contact_name', 'require', '请输入姓名', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['contact_duty', 'require', '请输入职务', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['contact_mobile', '/^1[3|4|5|8|9][0-9]\d{8}$/', '请输入手机', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['contact_tel', '/^\d{3,4}-?\d{7,8}$/', '请输入电话', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['contact_fax', '/^(\d{3,4}-)?\d{7,8}$/', '请输入传真', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['contact_email', 'email', '请输入邮箱', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['contact_qq', '/^[0-9]{0,20}$/', '请输入QQ', self::VALUE_VALIDATE, 'regex', self::MODEL_BOTH],
            ['contact_wechat', 'require', '请输入微信', self::VALUE_VALIDATE, 'regex', self::MODEL_BOTH],
        ];

        // 保存 检查的和新增一致 还需要检查状态
        $validateTable[COMPANY_ACTION_SAVE] = $validateTable[COMPANY_ACTION_ADD];
        $validateTable[COMPANY_ACTION_SAVE][] = ['company_id', 'validateCanSaveInfo', '已提交申请,无法修改资料', self::MUST_VALIDATE, 'callback', self::MODEL_BOTH];

        // 认证提交 检查的和新增一致 还需要检查状态
        $validateTable[COMPANY_ACTION_APPLY] = $validateTable[COMPANY_ACTION_ADD];
        $validateTable[COMPANY_ACTION_APPLY][] = ['company_id', 'validateCanCompanyApply', '当前状态无法提交', self::MUST_VALIDATE, 'callback', self::MODEL_BOTH];

        // 通过认证
        $validateTable[COMPANY_ACTION_PASS_APPLY] = [
            ['company_id', 'require', 'empty company_id', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['company_id', 'validateCanPassApply', '当前状态无法进行通过操作', self::MUST_VALIDATE, 'callback', self::MODEL_BOTH],
        ];

        // 拒绝认证
        $validateTable[COMPANY_ACTION_REFUSE_APPLY] = [
            ['company_id', 'require', 'empty company_id', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
            ['company_id', 'validateCanRefuseApply', '当前状态无法进行拒绝操作', self::MUST_VALIDATE, 'callback', self::MODEL_BOTH],
            ['audit_reason', 'require', '请输入拒绝理由', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH],
        ];
        return isset($validateTable[$action]) ? $validateTable[$action] : [];
    }

    /**
     * 根据操作获取完成规则
     * @param int $action
     * @param array $request
     * @return array
     * User: hjun
     * Date: 2019-01-13 15:06:04
     * Update: 2019-01-13 15:06:04
     * Version: 1.00
     */
    public function getAutoByAction($action = COMPANY_ACTION_ADD, $request = [])
    {
        $autoTable = [];
        $autoTable[COMPANY_ACTION_ADD] = [
            ['establishment_time', strtotime($request['establishment_time']), self::MODEL_INSERT, 'string'],
            ['create_time', NOW_TIME, self::MODEL_INSERT, 'string'],
            ['save_time', NOW_TIME, self::MODEL_INSERT, 'string'],
        ];
        $autoTable[COMPANY_ACTION_SAVE] = [
            ['establishment_time', strtotime($request['establishment_time']), self::MODEL_UPDATE, 'string'],
            ['save_time', NOW_TIME, self::MODEL_UPDATE, 'string'],
        ];
        $autoTable[COMPANY_ACTION_APPLY] = [
            ['establishment_time', strtotime($request['establishment_time']), self::MODEL_BOTH, 'string'],
            ['audit_status', COMPANY_AUDIT_STATUS_WAIT, self::MODEL_BOTH, 'string'],
            ['company_apply_status', COMPANY_APPLY_STATUS_YES, self::MODEL_BOTH, 'string'],
            ['company_apply_time', NOW_TIME, self::MODEL_BOTH, 'string'],
        ];
        // 提交认证时 未有数据 则要完成创建时间
        if ($action === COMPANY_ACTION_APPLY && !$this->validateHasCompany()) {
            $autoTable[COMPANY_ACTION_APPLY] = array_merge($autoTable[COMPANY_ACTION_APPLY], [
                ['create_time', NOW_TIME, self::MODEL_BOTH, 'string'],
                ['save_time', NOW_TIME, self::MODEL_BOTH, 'string'],
            ]);
        }
        $autoTable[COMPANY_ACTION_PASS_APPLY] = [
            ['auth_status', COMPANY_AUTH_STATUS_YES, self::MODEL_UPDATE, 'string'],
            ['auth_time', NOW_TIME, self::MODEL_UPDATE, 'string'],
            ['audit_status', COMPANY_AUDIT_STATUS_PASS, self::MODEL_UPDATE, 'string'],
            ['audit_time', NOW_TIME, self::MODEL_UPDATE, 'string'],
            ['pass_snapshot', $this->autoPassSnapshot($request['company_id']), self::MODEL_UPDATE, 'string'],
        ];
        $autoTable[COMPANY_ACTION_REFUSE_APPLY] = [
            ['auth_status', COMPANY_AUTH_STATUS_NO, self::MODEL_UPDATE, 'string'],
            ['audit_status', COMPANY_AUDIT_STATUS_REFUSE, self::MODEL_UPDATE, 'string'],
            ['audit_time', NOW_TIME, self::MODEL_UPDATE, 'string'],
            ['audit_reason', $request['audit_reason'], self::MODEL_UPDATE, 'string'],
        ];
        return isset($autoTable[$action]) ? $autoTable[$action] : [];
    }

    /**
     * 根据操作获取 数据库操作类型
     * @param int $action
     * @param array $request
     * @return int
     * User: hjun
     * Date: 2019-01-13 15:06:26
     * Update: 2019-01-13 15:06:26
     * Version: 1.00
     */
    public function getTypeByAction($action = COMPANY_ACTION_ADD, $request = [])
    {
        $typeTable = [];
        $typeTable[COMPANY_ACTION_ADD] = self::MODEL_INSERT;
        $typeTable[COMPANY_ACTION_SAVE] = self::MODEL_UPDATE;
        $typeTable[COMPANY_ACTION_APPLY] = self::MODEL_UPDATE;
        if ($action == COMPANY_ACTION_APPLY && !$this->validateHasCompany()) {
            $typeTable[COMPANY_ACTION_APPLY] = self::MODEL_INSERT;
        }
        $typeTable[COMPANY_ACTION_PASS_APPLY] = self::MODEL_UPDATE;
        $typeTable[COMPANY_ACTION_REFUSE_APPLY] = self::MODEL_UPDATE;
        return isset($typeTable[$action]) ? $typeTable[$action] : self::MODEL_UPDATE;
    }

    /**
     * 根据操作获取描述
     * @param int $action
     * @param array $request
     * @return string
     * User: hjun
     * Date: 2019-01-13 18:26:59
     * Update: 2019-01-13 18:26:59
     * Version: 1.00
     */
    public function getDescByAction($action = COMPANY_ACTION_ADD, $request = [])
    {
        $descTable = [];
        $descTable[COMPANY_ACTION_ADD] = '录入企业信息';
        $descTable[COMPANY_ACTION_SAVE] = '修改企业信息';
        $descTable[COMPANY_ACTION_APPLY] = '提交企业认证申请';
        $descTable[COMPANY_ACTION_PASS_APPLY] = '通过企业认证';
        $descTable[COMPANY_ACTION_REFUSE_APPLY] = '拒绝企业认证';
        return isset($descTable[$action]) ? $descTable[$action] : '';
    }

    /**
     * 获取企业的账号
     * @param int $companyId
     * @return array
     * User: hjun
     * Date: 2019-01-17 02:28:51
     * Update: 2019-01-17 02:28:51
     * Version: 1.00
     */
    public function getAdminByCompanyFromCache($companyId = 0)
    {
        $info = $this->getLastQueryData("admin_{$companyId}");
        if (empty($info)) {
            $where = [];
            $where['company_id'] = $companyId;
            $info = D('Admin')->where($where)->find();
            $this->setLastQueryData("admin_{$companyId}", $info);
        }
        return $info;
    }

    /**
     * 企业操作
     * @param int $action
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-13 14:44:47
     * Update: 2019-01-13 14:44:47
     * Version: 1.00
     */
    public function action($action = COMPANY_ACTION_ADD, $request = [])
    {
        $fields = $this->getFieldsByAction($action, $request);
        $validate = $this->getValidateByAction($action, $request);
        $auto = $this->getAutoByAction($action, $request);
        $type = $this->getTypeByAction($action, $request);
        $result = $this->getAndValidateDataFromRequest($fields, $request, $validate, $auto, $type);
        if (!isSuccess($result)) {
            return $result;
        }

        $this->startTrans();
        // 保存数据
        $admin = $this->getAdminFromCache();
        // 管理员的话企业ID是从请求中获取 普通账号则是在admin中获取
        $companyId = isSystemAdmin($admin) ? $request['company_id'] : $admin['company_id'];
        $results = [];
        $data = $result['data'];
        if ($type == self::MODEL_INSERT) {
            $oldData = [];
            $companyId = $this->add($data);
            $data['company_id'] = $companyId;
            $results[] = $companyId;
            // 如果是新增 则admin表要记录company_id session和admin都需要更改
            $where = [];
            $where['id'] = $admin['id'];
            $results[] = D('Admin')->where($where)->setField('company_id', $companyId);
            session('ADMIN.company_id', $companyId);
        } else {
            $oldData = $this->getCompanyFromCache($companyId);
            $data['company_id'] = $companyId;
            $results[] = $this->save($data);
        }

        // 如果是审核通过, 则要将其账号角色修改为已认证企业
        if ($action === COMPANY_ACTION_PASS_APPLY) {
            $companyAdmin = $this->getAdminByCompanyFromCache($data['company_id']);
            $roleId = D('Role')->getPassCompanyRoleId();
            $results[] = M('role_user')->where(array('user_id' => $companyAdmin['id']))->delete();
            if (!empty($roleId)) {
                $roleData = array();
                $roleData['role_id'] = $roleId;
                $roleData['user_id'] = $companyAdmin['id'];
                $results[] = M('role_user')->add($roleData);
            }
        }

        $data = array_merge($oldData, $data);

        // 如果已经认证 说明可能有提交数据申报 如果企业名称发生变化 需要更新数据申报表的企业名称
        if ($this->isAuth($oldData) && $oldData['company_name'] != $data['company_name']) {
            $options = [];
            $options['id_key'] = 'company_id';
            $options['id'] = $data['company_id'];
            $options['name_key'] = 'company_name';
            $options['name'] = $data['company_name'];
            $options['tables'] = ['data_apply', 'qnr_answer'];
            $results[] = syncUpdateName($options);
        }
        // 记日志
        $desc = $this->getDescByAction($action);
        $results[] = D('CompanyLog')->addLog($action, $oldData, $data, $request, $desc);

        if (in_array(false, $results, true)) {
            $this->rollback();
            return getReturn(CODE_ERROR);
        }
        $this->commit();
        return getReturn(CODE_SUCCESS, 'success', $data);
    }

    /**
     * 保存企业信息
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-12 22:06:04
     * Update: 2019-01-12 22:06:04
     * Version: 1.00
     */
    public function saveInfo($request = [])
    {
        if ($this->validateHasCompany()) {
            $action = COMPANY_ACTION_SAVE;
        } else {
            $action = COMPANY_ACTION_ADD;
        }
        $result = $this->action($action, $request);
        return $result;
    }

    /**
     * 申请认证
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-12 23:03:19
     * Update: 2019-01-12 23:03:19
     * Version: 1.00
     */
    public function companyApply($request = [])
    {
        $result = $this->action(COMPANY_ACTION_APPLY, $request);
        return $result;
    }

    /**
     * 通过认证申请
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-13 16:58:46
     * Update: 2019-01-13 16:58:46
     * Version: 1.00
     */
    public function passCompanyApply($request = [])
    {
        $result = $this->action(COMPANY_ACTION_PASS_APPLY, $request);
        if (isSuccess($result)) {
            $admin = $this->getAdminByCompanyFromCache($result['data']['company_id']);
            if (!empty($admin['username'])) {
                send_mail_long($admin['username'], "企业认证通知", "恭喜您,已通过认证");
            }
        }
        return $result;
    }

    /**
     * 拒绝认证申请
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-13 16:59:16
     * Update: 2019-01-13 16:59:16
     * Version: 1.00
     */
    public function refuseCompanyApply($request = [])
    {
        $result = $this->action(COMPANY_ACTION_REFUSE_APPLY, $request);
        if (isSuccess($result)) {
            $admin = $this->getAdminByCompanyFromCache($result['data']['company_id']);
            if (!empty($admin['username'])) {
                send_mail_long($admin['username'], "企业认证通知", "很抱歉,认证申请被拒绝,原因:{$result['data']['audit_reason']}");
            }
        }
        return $result;
    }

    /**
     * 获取审核列表的搜索条件
     * @param array $request
     * @return mixed
     * User: hjun
     * Date: 2019-01-13 13:36:40
     * Update: 2019-01-13 13:36:40
     * Version: 1.00
     */
    public function getAuditListQueryWhere($request = [])
    {
        $where = [];
        // 审核状态
        if (!empty($request['audit_status'])) {
            $where['a.audit_status'] = $request['audit_status'] - 1;
        }
        // 公司名称
        if (!empty($request['company_name'])) {
            $where['a.company_name'] = ['LIKE', "%{$request['company_name']}%"];
        }
        // 申报状态
        if (!empty($request['data_apply_status'])) {
            $where['a.data_apply_status'] = $request['data_apply_status'] - 1;
        }
        // 认证时间
        $result = getRangeWhere($request, 'min_auth_time', 'max_auth_time');
        $authTimeWhere = $result['data'];
        if (!empty($authTimeWhere)) {
            $where['a.auth_time'] = $authTimeWhere;
        }
        return $where;
    }

    /**
     * 审核列表
     * 已提交的才显示
     * @param int $page
     * @param int $limit
     * @param array $queryWhere
     * @return array
     * User: hjun
     * Date: 2019-01-13 13:34:09
     * Update: 2019-01-13 13:34:09
     * Version: 1.00
     */
    public function getAuditListData($page = DF_PAGE, $limit = DF_PAGE_LIMIT, $queryWhere = [])
    {
        $fields = [
            'company_id', 'company_name', 'create_time', 'audit_status', 'audit_time', 'audit_reason',
            'auth_status', 'auth_time', 'data_apply_time', 'company_apply_status', 'company_apply_time'
        ];
        $fields = implode(',', $fields);
        $where = [];
        $where['a.company_apply_status'] = COMPANY_DATA_APPLY_STATUS_YES;
        $where['a.is_delete'] = NOT_DELETED;
        $where = array_merge($where, $queryWhere);
        $order = 'create_time DESC';
        $options = [];
        $options['alias'] = 'a';
        $options['field'] = $fields;
        $options['where'] = $where;
        $options['page'] = $page;
        $options['limit'] = $limit;
        $options['order'] = $order;
        $total = $this->getCount($options);
        if (empty($total)) {
            $list = [];
        } else {
            $list = $this->queryList($options);
        }
        $data = [];
        $data['total'] = $total;
        $data['list'] = $list;
        return $data;
    }

    /**
     * 获取管理首页的数量数据
     * @return array
     * User: hjun
     * Date: 2019-01-19 02:24:31
     * Update: 2019-01-19 02:24:31
     * Version: 1.00
     */
    public function getAdminIndexMeta()
    {
        $data = [];
        $data['audit_status_0'] = 0;
        $data['audit_status_1'] = 0;
        $data['audit_status_2'] = 0;
        $data['auth_status_1'] = 0;
        $field = [
            'COUNT(1) num', 'auth_status', 'audit_status'
        ];
        $where = [];
        $where['is_delete'] = NOT_DELETED;
        $where['company_apply_status'] = COMPANY_APPLY_STATUS_YES;
        $group = 'auth_status,audit_status';
        $options = [];
        $options['field'] = $field;
        $options['where'] = $where;
        $options['group'] = $group;
        $list = $this->queryList($options);
        if (empty($list)) {
            return $data;
        }
        foreach ($list as $meta) {
            if ($meta['auth_status'] == COMPANY_AUTH_STATUS_YES) {
                // 认证数量
                $data['auth_status_1'] += $meta['num'];
                $data['audit_status_1'] += $meta['num'];
            } else {
                if ($meta['audit_status'] == COMPANY_AUDIT_STATUS_WAIT) {
                    $data['audit_status_0'] += $meta['num'];
                } elseif ($meta['audit_status'] == COMPANY_AUDIT_STATUS_REFUSE) {
                    $data['audit_status_2'] += $meta['num'];
                }
            }
        }
        return $data;
    }
}