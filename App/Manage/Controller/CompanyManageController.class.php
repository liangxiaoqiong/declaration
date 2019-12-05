<?php
/**
 * 企业管理
 * Class CompanyManageController
 * @package Manage\Controller
 * User: czx
 * Date: 2019-01-12 20:22:33
 * Update: 2019-01-12 20:22:33
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */

namespace Manage\Controller;

use Common\Util\Excel;

class CompanyManageController extends CommonController
{

    /**
     * 审核列表
     * User: hjun
     * Date: 2019-01-12 19:37:14
     * Update: 2019-01-12 19:37:14
     * Version: 1.00
     */
    public function auditList()
    {
        $model = D('Company');
        if ($this->req['action'] == 'query') {
            $where = $model->getAuditListQueryWhere($this->req);
            $data = $model->getAuditListData($this->req['page'], $this->req['limit'], $where);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        } elseif ($this->req['action'] == 'export') {
            $where = $model->getAuditListQueryWhere($this->req);
            $data = $model->getAuditListData(1, 0, $where);
            $list = $data['list'];
            if (empty($list)) {
                $this->error("暂无可导出数据");
            }
            foreach ($list as $key => $value) {
                $value['data_apply_time'] = date('Y-m-d', $value['data_apply_time']);
                $value['company_apply_time'] = date('Y-m-d H:i:s', $value['company_apply_time']);
                if ($model->isAuth($value)) {
                    $value['audit_desc'] = '通过';
                } elseif ($model->isRefuseAudit($value)) {
                    $value['audit_desc'] = '拒绝，原因:' . $value['audit_reason'];
                } else {
                    $value['audit_desc'] = '待审核';
                }
                $list[$key] = $value;
            }
            $map = [
                '公司名称' => 'company_name',
                '数据申报时间' => 'data_apply_time',
                '认证申请时间' => 'company_apply_time',
                '审核状态' => 'audit_desc',
            ];
            $excel = new Excel($map, $list);
            $excel->export();
        }
        $this->display();
    }

    /**
     * 企业信息
     * User: hjun
     * Date: 2019-01-12 19:37:27
     * Update: 2019-01-12 19:37:27
     * Version: 1.00
     */
    public function companyInfo()
    {
        if ($this->req['action'] == 'get') {
            $data = D('Company')->getCompany($this->req['company_id']);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        }
    }

    /**
     * 审核企业
     * User: hjun
     * Date: 2019-01-12 19:37:38
     * Update: 2019-01-12 19:37:38
     * Version: 1.00
     */
    public function auditCompany()
    {
        if (IS_POST) {
            if ($this->req['action'] == 'pass') {
                $result = D('Company')->passCompanyApply($this->req);
                $action = '通过';
            } elseif ($this->req['action'] == 'refuse') {
                $result = D('Company')->refuseCompanyApply($this->req);
                $action = '拒绝';
            }
            if (isSuccess($result)) {
                addAdminLog($this->admin, $action . '企业认证申请,企业ID:' . $this->req['company_id']);
            }
            $this->apiResponse($result);
        }
    }

    /**
     * 发送邮件
     * User: hjun
     * Date: 2019-01-12 19:37:47
     * Update: 2019-01-12 19:37:47
     * Version: 1.00
     */
    public function sendEmail()
    {
        $admin = D('Company')->getAdminByCompanyFromCache($this->req['company_id']);
        $result = send_mail_long($admin['username'], $this->req['title'], $this->req['message'], $this->req['attachment']);
        if ($result === true) {
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success'));
        } else {
            $this->apiResponse(getReturn(CODE_ERROR, '发送失败,请联系管理员', $result));
        }
    }
}
