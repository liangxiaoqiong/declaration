<?php
/**
 * Class CrontabController
 *
 * 定时任务
 *
 * @author    hjun
 * @version  1.0
 * @company 厦门市虚拟与增强显示产业协会
 * @Copyright  ©2018   All rights reserved
 */

namespace Manage\Controller;

class CrontabController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 每个月1号, 重置数据申报状态
     * User: hjun
     * Date: 2019-01-30 14:54:59
     * Update: 2019-01-30 14:54:59
     * Version: 1.00
     */
    public function resetDataApplyStatus()
    {
        // 1. 查询当月是否执行过该任务
        $modelCrontab = D('Crontab');
        $crontabName = 'reset_data_apply_status';
        $where = [];
        $where['status'] = 1;
        $where['crontab_name'] = $crontabName;
        $order = 'create_time DESC';
        $last = $modelCrontab->where($where)->order($order)->find();
        if (!empty($last)) {
            // 1.1 比较最近执行的时间是否是同月,是的话不需要执行
            $lastMonth = date('Y-m', $last['create_time']);
            $nowMonth = date('Y-m', NOW_TIME);
            if ($lastMonth == $nowMonth) {
                exit('already reset');
            }
        }

        // 2. 本月未执行过,则每个企业的数据申报状态都置为0
        $modelCompany = D('Company');
        $modelCrontab->startTrans();
        // 2.1 记录执行前哪些企业是申报的
        $where = [];
        $where['data_apply_status'] = COMPANY_DATA_APPLY_STATUS_YES;
        $oldIds = $modelCompany->where($where)->getField('company_id', true);
        $result = $modelCompany->where('1=1')->setField('data_apply_status', COMPANY_DATA_APPLY_STATUS_NO);
        if (false === $result) {
            exit('error');
        }
        $data = [];
        $data['crontab_name'] = $crontabName;
        $data['create_time'] = NOW_TIME;
        $data['status'] = 1;
        $data['log'] = "执行前:" . jsonEncode($oldIds) . "\nSQL:" . $modelCompany->_sql() . "\n结果:" . $result;
        $result = $modelCrontab->add($data);
        if (false === $result) {
            exit('error');
        }
        $modelCrontab->commit();
        exit('success');
    }
}
