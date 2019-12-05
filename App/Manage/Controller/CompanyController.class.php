<?php
/**
 * 企业信息
 * Class CompanyController
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

class CompanyController extends CommonController
{
    /**
     * 资料填写
     * User: hjun
     * Date: 2019-01-12 19:26:51
     * Update: 2019-01-12 19:26:51
     * Version: 1.00
     */
    public function companyInfo()
    {
        if (IS_POST && $this->req['action'] === 'save') {
            $result = D('Company')->saveInfo($this->req);
            if (isSuccess($result)) {
                addAdminLog($this->admin, "修改企业基本信息");
            }
            $this->apiResponse($result);
        } elseif ($this->req['action'] === 'get') {
            $info = D('Company')->getCompany($this->admin['company_id']);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $info));
        }
        $this->display();
    }

    /**
     * 认证提交
     * User: hjun
     * Date: 2019-01-12 19:28:07
     * Update: 2019-01-12 19:28:07
     * Version: 1.00
     */
    public function companyApply()
    {
        if (IS_POST) {
            $result = D('Company')->companyApply($this->req);
            if (isSuccess($result)) {
                addAdminLog($this->admin, "提交企业审核资料申请认证");
            }
            $this->apiResponse($result);
        }
    }

    /**
     * 申报列表
     * User: hjun
     * Date: 2019-01-12 19:36:17
     * Update: 2019-01-12 19:36:17
     * Version: 1.00
     */
    public function dataApplyList()
    {
        $model = D('DataApply');
        if ($this->req['action'] == 'query') {
            $where = $model->getApplyListQueryWhere($this->req);
            $data = $model->getCompanyDataApplyListData($this->req['page'], $this->req['limit'], $where);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        } elseif ($this->req['action'] == 'export') {
            $where = $model->getApplyListQueryWhere($this->req);
            $data = $model->getCompanyDataApplyListData(1, 0, $where);
            $list = $data['list'];
            foreach ($list as $key => $value) {
                $value['apply_time'] = date('Y-m', $value['apply_time']);
                $value['audit_status'] = $model->getAuditDesc($value['audit_status']);
                $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
                $list[$key] = $value;
            }
            $map = [
                '申报时间' => 'apply_time',
                '审核结果' => 'audit_status',
                '营业收入产值(万元)' => 'income_output_value',
                '出口额总值(万元)' => 'export_value',
                '投资额总价(万元)' => 'investment_value',
                '利润总额(万元)' => 'profit_value',
                '纳税总额(万元)' => 'taxes_value',
                '本月研发人员数(人)' => 'dev_people_value',
                '发明专利(个)' => 'invention_patent_value',
                '实用新型专利(个)' => 'utility_patent_value',
                '外观设计专利(个)' => 'facade_patent_value',
                '软件著作权(个)' => 'software_copyright_value',
                '其他' => 'other_value',
                '创建时间' => 'create_time',
            ];
            $excel = new Excel($map, $list);
            $excel->export();
        }
        $this->display('dataApply');
    }

    /**
     * 数据填报
     * User: hjun
     * Date: 2019-01-12 19:27:01
     * Update: 2019-01-12 19:27:01
     * Version: 1.00
     */
    public function dataApply()
    {
        if (IS_POST) {
            if ($this->req['action'] == 'add') {
                $result = D('DataApply')->addDataApply($this->req);
                $desc = "新增数据申报,企业ID:{$result['data']['company_id']}";
            } elseif ($this->req['action'] == 'save') {
                $result = D('DataApply')->saveDataApply($this->req);
                $desc = "修改数据申报,企业ID:{$result['data']['company_id']}";
            }
            if (isSuccess($result)) {
                addAdminLog($this->admin, $desc);
            }
            $this->apiResponse($result);
        } elseif ($this->req['action'] == 'get') {
            $data = D('DataApply')->getDataApply($this->req['data_id']);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        }
        $this->display();
    }

    /**
     * 2018数据填报
     * User: hjun
     * Date: 2019-01-12 19:27:01
     * Update: 2019-01-12 19:27:01
     * Version: 1.00
     */
    public function dataApply2018()
    {
        if (IS_POST) {
            if ($this->req['action'] == 'add') {
                $result = D('DataApply2018')->addDataApply($this->req);
                $desc = "新增2018数据申报,企业ID:{$result['data']['company_id']}";
            } elseif ($this->req['action'] == 'save') {
                $result = D('DataApply2018')->saveDataApply($this->req);
                $desc = "修改2018数据申报,企业ID:{$result['data']['company_id']}";
            }
            if (isSuccess($result)) {
                addAdminLog($this->admin, $desc);
            }
            $this->apiResponse($result);
        } elseif ($this->req['action'] == 'get') {
            $data = D('DataApply2018')->getDataApply2018($this->admin['company_id']);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        }
        $this->display();
    }

    /**
     * 我的问卷
     * User: hjun
     * Date: 2019-01-12 19:27:10
     * Update: 2019-01-12 19:27:10
     * Version: 1.00
     */
    public function myQnr()
    {
        $model = D('QnrAnswer');
        if (IS_POST) {
            if ($this->req['action'] === 'add') {
                // 新增回答
                $result = D('QnrAnswer')->addAnswer($this->req);
                $desc = "回答问卷,问卷ID:{$result['data']['qnr_id']}";
            } elseif ($this->req['action'] === 'save') {
                // 修改回答
                $result = D('QnrAnswer')->updateAnswer($this->req);
                $desc = "修改问卷答案,问卷ID:{$result['data']['qnr_id']}";
            }
            if (isSuccess($result)) {
                addAdminLog($this->admin, $desc);
            }
            $this->apiResponse($result);
        }
        if ($this->req['action'] === 'query') {
            // 获取我的问卷列表
            $this->req['company_id'] = $this->admin['company_id'];
            $where = $model->getAnswerListQueryWhere($this->req);
            $data = D('QnrAnswer')->getAnswerListData($this->req['page'], $this->req['limit'], $where);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        } elseif ($this->req['action'] === 'get') {
            // 获取我的问卷数据
            $data['answer_data'] = $model->getAnswer($this->req['answer_id']);
            $data['qnr_data'] = D('Qnr')->getQnr($data['answer_data']['qnr_id']);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        } elseif ($this->req['action'] === 'sync') {
            // 同步问卷
            $info = D('Qnr')->getQnr($this->req['qnr_id']);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $info));
        }
        $this->display();
    }
}
