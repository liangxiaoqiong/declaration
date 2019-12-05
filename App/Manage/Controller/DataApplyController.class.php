<?php
/**
 * 数据申报
 * Class DataApplyController
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

class DataApplyController extends CommonController
{

    /**
     * 申报列表
     * User: hjun
     * Date: 2019-01-12 19:36:41
     * Update: 2019-01-12 19:36:41
     * Version: 1.00
     */
    public function applyList()
    {
        $model = D('DataApply');
        if ($this->req['action'] == 'query') {
            $where = $model->getApplyListQueryWhere($this->req);
            $data = $model->getDataApplyListData($this->req['page'], $this->req['limit'], $where);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        } elseif ($this->req['action'] == 'export') {
            $where = $model->getApplyListQueryWhere($this->req);
            $data = $model->getDataApplyListData(1, 0, $where);
            $list = $data['list'];
            foreach ($list as $key => $value) {
                $value['apply_time'] = date('Y-m', $value['apply_time']);
                $value['audit_status'] = $model->getAuditDesc($value['audit_status']);
                $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
                $list[$key] = $value;
            }
            $map = [
                '企业名称' => 'company_name',
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
        $this->display();
    }

    /**
     * 查看申报数据
     * User: hjun
     * Date: 2019-01-14 01:40:52
     * Update: 2019-01-14 01:40:52
     * Version: 1.00
     */
    public function dataApply()
    {
        if ($this->req['action'] == 'get') {
            $data = D('DataApply')->getDataApply($this->req['data_id']);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        }
    }

    /**
     * 申报审核
     * User: hjun
     * Date: 2019-01-12 19:36:57
     * Update: 2019-01-12 19:36:57
     * Version: 1.00
     */
    public function audit()
    {
        if (IS_POST) {
            if ($this->req['action'] == 'pass') {
                $result = D('DataApply')->passDataApply($this->req);
                if (isSuccess($result)) {
                    addAdminLog($this->admin, "通过数据申报,数据ID:{$result['data']['data_id']}");
                }
                $this->apiResponse($result);
            } elseif ($this->req['action'] == 'refuse') {
                $result = D('DataApply')->refuseDataApply($this->req);
                if (isSuccess($result)) {
                    addAdminLog($this->admin, "拒绝数据申报,数据ID:{$result['data']['data_id']}");
                }
                $this->apiResponse($result);
            }
        }
    }



    /**
     * 申报列表
     * User: hjun
     * Date: 2019-01-12 19:36:41
     * Update: 2019-01-12 19:36:41
     * Version: 1.00
     */
    public function applyList2018()
    {
        $model = D('DataApply2018');
        if ($this->req['action'] == 'query') {
            $where = $model->getApplyListQueryWhere($this->req);
            $data = $model->getDataApplyListData($this->req['page'], $this->req['limit'], $where);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        } elseif ($this->req['action'] == 'export') {
            $where = $model->getApplyListQueryWhere($this->req);
            $data = $model->getDataApplyListData(1, 0, $where);
            $list = $data['list'];
            foreach ($list as $key => $value) {
                $value['apply_time'] = date('Y', $value['apply_time']);
                $value['audit_status'] = $model->getAuditDesc($value['audit_status']);
                $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
                $list[$key] = $value;
            }
            $map = [
                '企业名称' => 'company_name',
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
        $this->display();
    }

    /**
     * 查看申报数据
     * User: hjun
     * Date: 2019-01-14 01:40:52
     * Update: 2019-01-14 01:40:52
     * Version: 1.00
     */
    public function dataApply2018()
    {
        if ($this->req['action'] == 'get') {
            $data = D('DataApply2018')->getDataApply($this->req['data_id']);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        }
    }

    /**
     * 申报审核
     * User: hjun
     * Date: 2019-01-12 19:36:57
     * Update: 2019-01-12 19:36:57
     * Version: 1.00
     */
    public function audit2018()
    {
        if (IS_POST) {
            if ($this->req['action'] == 'pass') {
                $result = D('DataApply2018')->passDataApply($this->req);
                if (isSuccess($result)) {
                    addAdminLog($this->admin, "通过2018数据申报,数据ID:{$result['data']['data_id']}");
                }
                $this->apiResponse($result);
            } elseif ($this->req['action'] == 'refuse') {
                $result = D('DataApply2018')->refuseDataApply($this->req);
                if (isSuccess($result)) {
                    addAdminLog($this->admin, "拒绝2018数据申报,数据ID:{$result['data']['data_id']}");
                }
                $this->apiResponse($result);
            }
        }
    }
}
