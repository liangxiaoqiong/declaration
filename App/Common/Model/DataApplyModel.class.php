<?php

namespace Common\Model;

use Common\Interfaces\Action;

/**
 * 数据申报
 * Class DataApply
 * @package Common\Model
 * User: czx
 * Date: 2019-1-12 14:11:45
 * Update: 2018-05-25 14:11:45
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
class DataApplyModel extends BaseModel implements Action
{
    protected $tableName = "data_apply";

    /**
     * 产值分析汇总数据获取
     * @param array $condition
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: czx
     * Date: 2019-01-12 22:39:50
     * Update: 2019-01-12 22:39:50
     * Version: 1.00
     */
    public function getOutputValue($page = 1, $limit = 20, $map = array())
    {
        $field = [
            'a.company_id company_id',
            'b.company_name company_name',
            'SUM(a.income_output_value) income_output_value',
            'SUM(a.export_value) export_value',
            'SUM(a.investment_value) investment_value',
            'SUM(a.profit_value) profit_value',
            'SUM(a.taxes_value) taxes_value',
        ];
        $where = array();
        $condition = array_merge($where, $map);
        $returnListData = $this->field(implode(",", $field))
            ->alias('a')
            ->join('LEFT JOIN __COMPANY__ b  on a.company_id = b.company_id')
            ->where($condition)
            ->group('a.company_id')
            ->limit(($page - 1) * $limit, $limit)
            ->select();

        $returnTotalData = $this->field(implode(",", $field))
            ->alias('a')
            ->join('LEFT JOIN __COMPANY__ b  on a.company_id = b.company_id')
            ->where($condition)
            ->group('a.company_id')
            ->select();

        $totalItem = 0;
        $total_income_output_value = 0;
        $total_export_value = 0;
        $total_investment_value = 0;
        $total_profit_value = 0;
        $total_taxes_value = 0;
        foreach ($returnTotalData as $key => $value) {
            $totalItem++;
            $total_income_output_value += $value['income_output_value']; //产值总额
            $total_export_value += $value['export_value']; //出口额总值
            $total_investment_value += $value['investment_value']; //投资额总值
            $total_profit_value += $value['profit_value'];  //利润总额
            $total_taxes_value += $value['taxes_value']; //税收总额
        }

        $data = array();
        $data['total'] = $totalItem;
        $data['output_list_data'] = $returnListData;
        $data['total_income_output_value'] = $total_income_output_value;
        $data['total_export_value'] = $total_export_value;
        $data['total_investment_value'] = $total_investment_value;
        $data['total_profit_value'] = $total_profit_value;
        $data['total_taxes_value'] = $total_taxes_value;

        return getReturn(200, '成功', $data);
    }


    /**
     * 研发情况汇总明细数据获取
     * @param array $condition
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: czx
     * Date: 2019-01-12 22:39:50
     * Update: 2019-01-12 22:39:50
     * Version: 1.00
     */
    public function getdevelopDetail($page = 1, $limit = 10, $map = array())
    {
        /**
         * 研发人数 dev_people_value
         * 发明专利个数 invention_patent_value
         * 实用新型专利个数 utility_patent_value
         * 外观设计专利个数  facade_patent_value
         * 软件著作权个数 software_copyright_value
         * 其他  other_value
         */
        $field = [
            'a.company_id company_id',
            'b.company_name company_name',
            'MAX(a.dev_people_value) dev_people_value',
            'SUM(a.invention_patent_value) invention_patent_value',
            'SUM(a.utility_patent_value) utility_patent_value',
            'SUM(a.facade_patent_value) facade_patent_value',
            'SUM(a.software_copyright_value) software_copyright_value',
            'SUM(a.other_value) other_value',
        ];
        $where = array();
        $condition = array_merge($where, $map);
        $returnListData = $this->field(implode(",", $field))
            ->alias('a')
            ->join('LEFT JOIN __COMPANY__ b  on a.company_id = b.company_id')
            ->where($condition)
            ->group('a.company_id')
            ->limit(($page - 1) * $limit, $limit)
            ->select();

        $returnTotalData = $this->field(implode(",", $field))
            ->alias('a')
            ->join('LEFT JOIN __COMPANY__ b  on a.company_id = b.company_id')
            ->where($condition)
            ->group('a.company_id')
            ->select();

        $totalItem = 0;
        $total_dev_people_value = 0;
        $total_invention_patent_value = 0;
        $total_utility_patent_value = 0;
        $total_facade_patent_value = 0;
        $total_software_copyright_value = 0;
        $total_other_value = 0;
        foreach ($returnTotalData as $key => $value) {
            $totalItem++;
            $total_dev_people_value += $value['dev_people_value']; //研发人数
            $total_invention_patent_value += $value['invention_patent_value']; //发明专利个数
            $total_utility_patent_value += $value['utility_patent_value']; //实用新型专利个数
            $total_facade_patent_value += $value['facade_patent_value'];  //外观设计专利个数
            $total_software_copyright_value += $value['software_copyright_value']; //软件著作权个数
            $total_other_value += $value['other_value']; //其他
        }

        $data = array();
        $data['total'] = $totalItem;
        $data['develop_detail_data'] = $returnListData;
        $data['total_dev_people_value'] = $total_dev_people_value;
        $data['total_invention_patent_value'] = $total_invention_patent_value;
        $data['total_utility_patent_value'] = $total_utility_patent_value;
        $data['total_facade_patent_value'] = $total_facade_patent_value;
        $data['total_software_copyright_value'] = $total_software_copyright_value;
        $data['total_other_value'] = $total_other_value;

        return getReturn(200, '成功', $data);
    }

    /**
     * 产值分析明细
     * @param int $page
     * @param int $limit
     * @param array $map
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: czx
     * Date: 2019-01-13 15:27:50
     * Update: 2019-01-13 15:27:50
     * Version: 1.00
     */
    public function getOutputValueDetail($page = 1, $limit = 20, $map = array(), $action = "get")
    {
        $field = [
            'a.company_id company_id',
            'b.company_name company_name',
            'a.apply_time apply_time',
            'a.income_output_value income_output_value',
            'a.export_value export_value',
            'a.investment_value investment_value',
            'a.profit_value profit_value',
            'a.taxes_value taxes_value'];
        $where = array();
        $condition = array_merge($where, $map);
        $outputValueDetailData = $this->field(implode(",", $field))
            ->alias("a")
            ->join("LEFT JOIN __COMPANY__ b on a.company_id = b.company_id")
            ->where($condition)
            ->limit(($page - 1) * $limit, $limit)
            ->select();
        $totalItem = 0;
        if ($action == "get" || empty($action)) {
            $totalOutputValueDetailData = $this->alias("a")
                ->join("LEFT JOIN __COMPANY__ b on a.company_id = b.company_id")
                ->where($condition)
                ->select();
            $totalItem = count($totalOutputValueDetailData);
        }
        $data = array();
        $data['total'] = $totalItem;
        $data['output_detail_data'] = $outputValueDetailData;
        return getReturn(200, '成功', $data);
    }

    /**
     * 获取审核状态描述
     * @param int $status
     * @return string
     * User: hjun
     * Date: 2019-01-13 23:29:47
     * Update: 2019-01-13 23:29:47
     * Version: 1.00
     */
    public function getAuditDesc($status = DATA_AUDIT_STATUS_WAIT)
    {
        $descTable = [];
        $descTable[DATA_AUDIT_STATUS_WAIT] = '待审核';
        $descTable[DATA_AUDIT_STATUS_PASS] = '通过';
        $descTable[DATA_AUDIT_STATUS_REFUSE] = '拒绝';
        return $descTable[$status];
    }

    /**
     * 获取申报列表的查询条件
     * @param array $request
     * @return array
     * User: hjun
     * Date: 2019-01-13 23:02:29
     * Update: 2019-01-13 23:02:29
     * Version: 1.00
     */
    public function getApplyListQueryWhere($request = [])
    {
        $where = [];
        // 公司名称
        if (!empty($request['company_name'])) {
            $where['a.company_name'] = ['LIKE', "%{$request['company_name']}%"];
        }
        // 审核状态
        if (!empty($request['audit_status'])) {
            $where['a.audit_status'] = $request['audit_status'] - 1;
        }
        // 填报时间
        $result = getRangeWhere($request, 'min_apply_time', 'max_apply_time');
        $applyTimeWhere = $result['data'];
        if (!empty($applyTimeWhere)) {
            $where['a.apply_time'] = $applyTimeWhere;
        }
        return $where;
    }

    /**
     * 获取申报列表
     * @param int $page
     * @param int $limit
     * @param array $queryWhere
     * @return array
     * User: hjun
     * Date: 2019-01-13 22:25:52
     * Update: 2019-01-13 22:25:52
     * Version: 1.00
     */
    public function getDataApplyListData($page = DF_PAGE, $limit = DF_PAGE_LIMIT, $queryWhere = [])
    {
        $fields = [
            'data_id', 'company_id', 'company_name', 'apply_time', 'income_output_value',
            'export_value', 'investment_value', 'profit_value', 'taxes_value', 'dev_people_value',
            'invention_patent_value', 'utility_patent_value', 'facade_patent_value', 'software_copyright_value',
            'other_value', 'audit_status', 'audit_time', 'audit_reason', 'create_time'
        ];
        $fields = implode(',', $fields);
        $where = [];
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
     * 获取企业的申报列表 某个企业自己查看自己的列表
     * @param int $page
     * @param int $limit
     * @param array $queryWhere
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-13 22:22:20
     * Update: 2019-01-13 22:22:20
     * Version: 1.00
     */
    public function getCompanyDataApplyListData($page = DF_PAGE, $limit = DF_PAGE_LIMIT, $queryWhere = [])
    {
        $admin = $this->getAdmin();
        $queryWhere['a.company_id'] = $admin['company_id'];
        return $this->getDataApplyListData($page, $limit, $queryWhere);
    }

    /**
     * 获取数据报表数据
     * @param int $dataId
     * @return array
     * User: hjun
     * Date: 2019-01-14 00:32:53
     * Update: 2019-01-14 00:32:53
     * Version: 1.00
     */
    public function getDataApply($dataId = 0)
    {
        $where = [];
        $where['data_id'] = $dataId;
        $info = $this->field(true)->where($where)->find();
        return $info;
    }

    /**
     * 从缓存中获取数据表数据
     * @param int $dataId
     * @return array
     * User: hjun
     * Date: 2019-01-14 00:39:46
     * Update: 2019-01-14 00:39:46
     * Version: 1.00
     */
    public function getDataApplyFromCache($dataId = 0)
    {
        $info = $this->getLastQueryData("data_apply_{$dataId}");
        if (empty($info)) {
            $info = $this->getDataApply($dataId);
            $this->setLastQueryData("data_apply_{$dataId}", $info);
        }
        return $info;
    }

    /**
     * 判断能否新增申报
     * @return boolean
     * User: hjun
     * Date: 2019-01-14 00:12:08
     * Update: 2019-01-14 00:12:08
     * Version: 1.00
     */
    public function validateCanAddDataApply()
    {
        $admin = $this->getAdmin();
        $company = $this->getCompanyFromCache($admin['company_id']);
        // 已提交过不能再提交
        if ($this->isDataApply($company)) {
            $this->setValidateError('本月已提交数据申报,无需再提交');
            return false;
        }
        return true;
    }

    /**
     * 判断能否修改申报
     * @param int $dataId
     * @return boolean
     * User: hjun
     * Date: 2019-01-14 00:12:08
     * Update: 2019-01-14 00:12:08
     * Version: 1.00
     */
    public function validateCanSaveDataApply($dataId = 0)
    {
        $info = $this->getDataApplyFromCache($dataId);
        if (empty($info)) {
            $this->setValidateError("该数据报表已失效,无法修改");
            return false;
        }
        // 如果已经通过了不能修改
        if ($info['audit_status'] == DATA_AUDIT_STATUS_PASS) {
            $this->setValidateError('该数据报表已经通过审核,无法修改');
            return false;
        }
        return true;
    }

    /**
     * 判断能否通过申报
     * @param int $dataId
     * @return boolean
     * User: hjun
     * Date: 2019-01-14 00:12:08
     * Update: 2019-01-14 00:12:08
     * Version: 1.00
     */
    public function validateCanPassDataApply($dataId = 0)
    {
        $info = $this->getDataApplyFromCache($dataId);
        if (empty($info)) {
            $this->setValidateError("该数据报表已失效,无法操作");
            return false;
        }
        // 如果已经通过了不能修改
        if ($info['audit_status'] == DATA_AUDIT_STATUS_PASS) {
            $this->setValidateError('该数据报表已经通过审核,无需重复操作');
            return false;
        }
        // 被拒绝要等待重新修改
        if ($info['audit_status'] == DATA_AUDIT_STATUS_REFUSE) {
            $this->setValidateError('该数据报表已被拒绝,请等待企业重新填报');
            return false;
        }
        return true;
    }

    /**
     * 判断能否拒绝申报
     * @param int $dataId
     * @return boolean
     * User: hjun
     * Date: 2019-01-14 00:12:08
     * Update: 2019-01-14 00:12:08
     * Version: 1.00
     */
    public function validateCanRefuseDataApply($dataId = 0)
    {
        $info = $this->getDataApplyFromCache($dataId);
        if (empty($info)) {
            $this->setValidateError("该数据报表已失效,无法操作");
            return false;
        }
        // 被拒绝要等待重新修改
        if ($info['audit_status'] == DATA_AUDIT_STATUS_REFUSE) {
            $this->setValidateError('该数据报表已被拒绝,无需重复操作');
            return false;
        }
        // 如果已经通过了不能拒绝
        if ($info['audit_status'] == DATA_AUDIT_STATUS_PASS) {
            $this->setValidateError('该数据报表已经通过审核,无法操作');
            return false;
        }
        return true;
    }

    /**
     * 自动完成申报时间
     * 上个月的年月
     * @return string
     * User: hjun
     * Date: 2019-01-14 00:52:46
     * Update: 2019-01-14 00:52:46
     * Version: 1.00
     */
    public function autoApplyTime()
    {
        $now = strtotime(date('Y-m')); // 当月1日的时间
        $lastMonth = date('Y-m', $now - 3600 * 24); // 减去一天即为上个月
        $time = strtotime($lastMonth);
        return $time;
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
    public function getFieldsByAction($action = DATA_ACTION_ADD, $request = [])
    {
        $fieldsTable = [];
        $fieldsTable[DATA_ACTION_ADD] = [
            'company_id', 'company_name', 'apply_time', 'income_output_value', 'export_value',
            'investment_value', 'profit_value', 'taxes_value', 'dev_people_value', 'invention_patent_value',
            'utility_patent_value', 'facade_patent_value', 'software_copyright_value', 'other_value',
            'create_time', 'save_time',
        ];
        $fieldsTable[DATA_ACTION_SAVE] = [
            'apply_time', 'income_output_value', 'export_value', 'investment_value', 'profit_value',
            'taxes_value', 'dev_people_value', 'invention_patent_value', 'utility_patent_value',
            'facade_patent_value', 'software_copyright_value', 'other_value', 'save_time', 'audit_status',
        ];
        $fieldsTable[DATA_ACTION_PASS] = [
            'audit_status', 'audit_time'
        ];
        $fieldsTable[DATA_ACTION_REFUSE] = [
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
    public function getValidateByAction($action = DATA_ACTION_ADD, $request = [])
    {
        $validateTable = [];
        // 新增
        $validateTable[DATA_ACTION_ADD] = [
            ['company_id', 'validateCanAddDataApply', '本月已提交', self::MUST_VALIDATE, 'callback', self::MODEL_INSERT],
            ['income_output_value', 'double', '请输入营业收入产值(万元)', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
            ['export_value', 'double', '请输入出口额总额(万元)', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
            ['investment_value', 'double', '请输入投资额总价(万元)', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
            ['profit_value', 'double', '请输入利润总额(万元)', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
            ['taxes_value', 'double', '请输入纳税总额(万元)', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
            ['dev_people_value', 'number', '请输入本月研发人员数', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
            ['invention_patent_value', 'number', '请输入发明专利', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
            ['utility_patent_value', 'number', '请输入实用新型专利', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
            ['facade_patent_value', 'number', '请输入外观设计专利', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
            ['software_copyright_value', 'number', '请输入软件著作权', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
            ['other_value', 'number', '请输入其他', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
        ];

        // 保存
        $validateTable[DATA_ACTION_SAVE] = [
            ['data_id', 'validateCanSaveDataApply', '无法修改', self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
            ['income_output_value', 'double', '请输入营业收入产值(万元)', self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
            ['export_value', 'double', '请输入出口额总额(万元)', self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
            ['investment_value', 'double', '请输入投资额总价(万元)', self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
            ['profit_value', 'double', '请输入利润总额(万元)', self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
            ['taxes_value', 'double', '请输入纳税总额(万元)', self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
            ['dev_people_value', 'number', '请输入本月研发人员数', self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
            ['invention_patent_value', 'number', '请输入发明专利', self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
            ['utility_patent_value', 'number', '请输入实用新型专利', self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
            ['facade_patent_value', 'number', '请输入外观设计专利', self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
            ['software_copyright_value', 'number', '请输入软件著作权', self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
            ['other_value', 'number', '请输入其他', self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
        ];

        // 通过
        $validateTable[DATA_ACTION_PASS] = [
            ['data_id', 'validateCanPassDataApply', '无法通过', self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
        ];

        // 拒绝
        $validateTable[DATA_ACTION_REFUSE] = [
            ['data_id', 'validateCanRefuseDataApply', '无法拒绝', self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
            ['audit_reason', 'require', '请输入拒绝原因', self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
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
    public function getAutoByAction($action = DATA_ACTION_ADD, $request = [])
    {
        $autoTable = [];
        $autoTable[DATA_ACTION_ADD] = [
            ['company_id', $this->autoCompanyId(), self::MODEL_INSERT, 'string'],
            ['company_name', $this->autoCompanyName(), self::MODEL_INSERT, 'string'],
            ['apply_time', $this->autoApplyTime(), self::MODEL_INSERT, 'string'], // 默认上个月的时间
            ['create_time', NOW_TIME, self::MODEL_INSERT, 'string'],
            ['save_time', NOW_TIME, self::MODEL_INSERT, 'string'],
        ];

        $autoTable[DATA_ACTION_SAVE] = [
            ['audit_status', DATA_AUDIT_STATUS_WAIT, self::MODEL_UPDATE, 'string'],
            ['save_time', NOW_TIME, self::MODEL_UPDATE, 'string'],
        ];

        $autoTable[DATA_ACTION_PASS] = [
            ['audit_status', DATA_AUDIT_STATUS_PASS, self::MODEL_UPDATE, 'string'],
            ['audit_time', NOW_TIME, self::MODEL_UPDATE, 'string'],
        ];

        $autoTable[DATA_ACTION_REFUSE] = [
            ['audit_status', DATA_AUDIT_STATUS_REFUSE, self::MODEL_UPDATE, 'string'],
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
        $typeTable[DATA_ACTION_ADD] = self::MODEL_INSERT;
        $typeTable[DATA_ACTION_SAVE] = self::MODEL_UPDATE;
        $typeTable[DATA_ACTION_PASS] = self::MODEL_UPDATE;
        $typeTable[DATA_ACTION_REFUSE] = self::MODEL_UPDATE;
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
        $descTable[DATA_ACTION_ADD] = '新增数据申报';
        $descTable[DATA_ACTION_SAVE] = '修改数据申报';
        $descTable[DATA_ACTION_PASS] = '通过数据申报';
        $descTable[DATA_ACTION_REFUSE] = '拒绝数据申报';
        return isset($descTable[$action]) ? $descTable[$action] : '';
    }

    /**
     * 数据报表操作
     * @param int $action
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-13 23:41:24
     * Update: 2019-01-13 23:41:24
     * Version: 1.00
     */
    public function action($action = DATA_ACTION_ADD, $request = [])
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
        $results = [];
        $data = $result['data'];
        if ($type == self::MODEL_INSERT) {
            $oldData = [];
            $dataId = $this->add($data);
            $data['data_id'] = $dataId;
            $results[] = $dataId;
            // 新增的话 将企业设置为本月已提交
            $where = [];
            $where['company_id'] = $data['company_id'];
            $companyData = [];
            $companyData['data_apply_status'] = COMPANY_DATA_APPLY_STATUS_YES;
            $companyData['data_apply_time'] = $data['apply_time'];
            $results[] = D('Company')->where($where)->save($companyData);
        } else {
            $oldData = $this->getDataApplyFromCache($request['data_id']);
            $data['data_id'] = $oldData['data_id'];
            $results[] = $this->save($data);
        }

        $data = array_merge($oldData, $data);
        // 记日志
        $desc = $this->getDescByAction($action);
        $results[] = D('DataApplyLog')->addLog($action, $oldData, $data, $request, $desc);

        if (in_array(false, $results, true)) {
            $this->rollback();
            return getReturn(CODE_ERROR);
        }
        $this->commit();
        return getReturn(CODE_SUCCESS, 'success', $data);
    }

    /**
     * 新增数据申报
     * 可以新增的数据仅为上一个月的数据 新增后无法再新增
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-13 23:43:07
     * Update: 2019-01-13 23:43:07
     * Version: 1.00
     */
    public function addDataApply($request = [])
    {
        $result = $this->action(DATA_ACTION_ADD, $request);
        return $result;
    }

    /**
     * 修改数据申报
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-13 23:43:36
     * Update: 2019-01-13 23:43:36
     * Version: 1.00
     */
    public function saveDataApply($request = [])
    {
        $result = $this->action(DATA_ACTION_SAVE, $request);
        return $result;
    }

    /**
     * 通过数据申报
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-13 23:44:12
     * Update: 2019-01-13 23:44:12
     * Version: 1.00
     */
    public function passDataApply($request = [])
    {
        $result = $this->action(DATA_ACTION_PASS, $request);
        return $result;
    }

    /**
     * 拒绝数据申报
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-13 23:44:47
     * Update: 2019-01-13 23:44:47
     * Version: 1.00
     */
    public function refuseDataApply($request = [])
    {
        $result = $this->action(DATA_ACTION_REFUSE, $request);
        return $result;
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
        $field = [
            'COUNT(1) num', 'audit_status'
        ];
        $where = [];
        $where['is_delete'] = NOT_DELETED;
        $group = 'audit_status';
        $options = [];
        $options['field'] = $field;
        $options['where'] = $where;
        $options['group'] = $group;
        $list = $this->queryList($options);
        if (empty($list)) {
            return $data;
        }
        foreach ($list as $meta) {
            if ($meta['audit_status'] == DATA_AUDIT_STATUS_WAIT) {
                $data['audit_status_0'] += $meta['num'];
            } elseif ($meta['audit_status'] == DATA_AUDIT_STATUS_PASS) {
                $data['audit_status_1'] += $meta['num'];
            } elseif ($meta['audit_status'] == COMPANY_AUDIT_STATUS_REFUSE) {
                $data['audit_status_2'] += $meta['num'];
            }
        }
        return $data;
    }
}