<?php
/**
 * 统计
 * Class DataAnalysisController
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
use Common\Util\PHPExcel;

class DataAnalysisController extends BaseController
{
    /**统计分析*/
    public function index()
    {
        $this->display();
    }

    /**
     * 产值分析汇总
     * User: hjun
     * Date: 2019-01-12 19:38:16
     * Update: 2019-01-12 19:38:16
     * Version: 1.00
     */
    public function outputValueCount()
    {
        $request = $this->req;
        logWrite("产值分析汇总: " . json_encode($request, JSON_UNESCAPED_UNICODE));
        $checkData = $this->checkOutputValueData($request);
        if ($checkData['code'] != 200) {
            $this->ajaxReturn(getReturn(-1, $checkData['msg']));
        }
        $where = $this->getCommonAnalysisWhereData($request);
        if (!empty($request['start_time']) && !empty($request['end_time'])) {
            $where['a.apply_time'] =
                array('between', array(strtotime($request['start_time']), strtotime($request['end_time'])));
        } else {
            if (!empty($request['start_time'])) {
                $where['a.apply_time'] = array('gt', strtotime($request['start_time']));
            }

            if (!empty($request['end_time'])) {
                $where['a.apply_time'] = array('elt', strtotime($request['end_time']));
            }
        }
        if ($request['action'] == 'get') {
            $returnData = D("DataApply")->getOutputValue($request['page'], $request['limit'], $where);
            logWrite("产值分析汇总data: " . json_encode($returnData, JSON_UNESCAPED_UNICODE));
            $this->ajaxReturn($returnData);
        } else if ($request['action'] == 'export') {
            $returnData = D("DataApply")->getOutputValue($request['page'], 0, $where);
            $this->exportOutputValueData($returnData['data']);
        }
        $this->display();

    }

    /**
     * 研发情况汇总明细
     * User: hjun
     * Date: 2019-01-12 19:38:46
     * Update: 2019-01-12 19:38:46
     * Version: 1.00
     */
    public function developDetail()
    {
        $request = $this->req;
        logWrite("研发情况汇总明细汇总: " . json_encode($request, JSON_UNESCAPED_UNICODE));
        $checkData = $this->checkOutputValueData($request);
        if ($checkData['code'] != 200) {
            $this->ajaxReturn(getReturn(-1, $checkData['msg']));
        }
        $where = $this->getCommonAnalysisWhereData($request);

        if (!empty($request['start_time']) && !empty($request['end_time'])) {
            $where['a.apply_time'] =
                array('between', array(strtotime($request['start_time']), strtotime($request['end_time'])));
        } else {
            if (!empty($request['start_time'])) {
                $where['a.apply_time'] = array('gt', strtotime($request['start_time']));
            }

            if (!empty($request['end_time'])) {
                $where['a.apply_time'] = array('elt', strtotime($request['end_time']));
            }
        }


        if ($request['action'] == 'get') {
            $returnData = D("DataApply")->getdevelopDetail($request['page'], $request['limit'], $where);
            logWrite("研发情况汇总明细汇总data: " . json_encode($returnData, JSON_UNESCAPED_UNICODE));
            $this->ajaxReturn($returnData);
        } else if ($request['action'] == 'export') {
            $returnData = D("DataApply")->getdevelopDetail($request['page'], 0, $where);
            $this->exportDevelopDetailData($returnData['data']);
        }
        $this->display();
    }

    /**
     * 产值分析明细
     * User: hjun
     * Date: 2019-01-12 19:38:58
     * Update: 2019-01-12 19:38:58
     * Version: 1.00
     */
    public function outputValueDetail()
    {

        $request = $this->req;
        logWrite("产值分析明细: " . json_encode($request, JSON_UNESCAPED_UNICODE));
        $checkData = $this->checkOutputValueData($request);
        if ($checkData['code'] != 200) {
            $this->ajaxReturn(getReturn(-1, $checkData['msg']));
        }
        $where = $this->getCommonAnalysisWhereData($request);
        // $where['unit'] = $request['unit'];  //unit 0:天  1:季度  2:所有
        if (!empty($request['start_time'])) {
            if (empty($request['end_time'])) {
                $this->ajaxReturn(getReturn(-1, "请选择结束时间"));
            }
        }

        if (!empty($request['end_time'])) {
            if (empty($request['start_time'])) {
                $this->ajaxReturn(getReturn(-1, "请选择开始时间"));
            }
        }
        $year = date("Y", time());
        $end_time = time();
        $start_time = strtotime($year . "-01");
        if (!empty($request['start_time']) && !empty($request['end_time'])) {
            $end_time = strtotime($request['end_time']);
            $start_time = strtotime($request['start_time']);
            if ($end_time - $start_time > 365 * 24 * 3600) {
                $this->ajaxReturn(getReturn(-1, "搜搜时间只能小于一年"));
            }
        }
        if (!empty($end_time) && !empty($start_time)) {
            $yes_syear = date("Y", $start_time) - 1;
            $yes_smouth = date("m", $start_time);
            $yes_start_time = strtotime($yes_syear . "-" . $yes_smouth);

            $yes_eyear = date("Y", $end_time) - 1;
            $yes_emouth = date("m", $end_time);
            $yes_end_time = strtotime($yes_eyear . "-" . $yes_emouth);
            $where['a.apply_time'] = array(
                array('between', array($start_time, $end_time)),
                array('between', array($yes_start_time, $yes_end_time)),
                'or');
        }

        if ($request['action'] == 'get') {
            $returnData = D("DataApply")->getOutputValueDetail($request['page'], $request['limit'], $where, "get");
            $this->ajaxReturn($returnData);
        } else if ($request['action'] == 'export') {
            $returnData = D("DataApply")->getOutputValueDetail($request['page'], 0, $where, "export");
            $exportData = $this->getExportOutputValueDetail($returnData['data']['output_detail_data'], $request['unit'], $start_time, $end_time);
            if ($exportData['code'] != 200){
                $this->ajaxReturn($exportData);
            }
            $this->exportOutputValueDetailData($exportData['data']);
        }
        $this->display();
    }

    /**
     * 公共的查询条件
     * @param $request
     * @return array
     * User: czx
     * Date: 2019-01-12 21:50:28
     * Update: 2019-01-12 21:50:28
     * Version: 1.00
     */
    public function getCommonAnalysisWhereData($request)
    {
        $where = array();
        if (!empty($request['company_form'])) { //公司性质
            $where['b.company_form'] = $request['company_form'];
        }
        if ($request['is_high_new'] == 1) { //是否为高新企业
            $where['b.is_high_new'] = $request['is_high_new'];
        }
        if (!empty($request['capital_form'])) { //所有制形式
            $where['b.capital_form'] = $request['capital_form'];
        }
        if ($request['is_hot'] == 1) { //是否为热门企业
            $where['b.is_hot'] = $request['is_hot'];
        }
        if (!empty($request['area_id_1'])) { //省
            $where['b.area_id_1'] = $request['area_id_1'];
        }
        if (!empty($request['area_id_2'])) { //市
            $where['b.area_id_2'] = $request['area_id_2'];
        }
        if (!empty($request['area_id_3'])) { //区
            $where['b.area_id_3'] = $request['area_id_3'];
        }

        $where['a.audit_status'] = 1;
        return $where;
    }

    /**
     * 产值分析汇总条件检查
     * @param $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: czx
     * Date: 2019-01-12 23:14:12
     * Update: 2019-01-12 23:14:12
     * Version: 1.00
     */
    public function checkOutputValueData($request)
    {
        if (!empty($request['start_time']) && !empty($request['end_time'])) {
            if ($request['end_time'] < $request['start_time']) {
                return getReturn(-1, '搜索开始时间不能大于结束时间');
            }
        }
        return getReturn(200, 'success');
    }

    /**
     * 导出产值分析汇总
     * @param $data
     * User: czx
     * Date: 2019-01-13 01:14:05
     * Update: 2019-01-13 01:14:05
     * Version: 1.00
     */
    public function exportOutputValueData($data)
    {

        $map = [
            '企业名称' => 'company_name',
            '产值总额' => 'income_output_value',
            '出口额总值' => 'export_value',
            '投资额总值' => 'investment_value',
            '利润总额' => 'profit_value',
            '税收总额' => 'taxes_value',
        ];
        $excel = new Excel($map, $data['output_list_data']);
        $excel->setFileName("产值分析汇总");
        $excel->export();
    }

    /**
     * 研发情况汇总明细
     * @param $data
     * User: czx
     * Date: 2019-01-13 01:14:05
     * Update: 2019-01-13 01:14:05
     * Version: 1.00
     */
    public function exportDevelopDetailData($data)
    {
        $map = [
            '企业名称' => 'company_name',
            '研发人数' => 'dev_people_value',
            '发明专利个数' => 'invention_patent_value',
            '实用新型专利个数' => 'utility_patent_value',
            '外观设计专利个数' => 'facade_patent_value',
            '软件著作权个数' => 'software_copyright_value',
            '其他' => 'other_value',
        ];
        $excel = new Excel($map, $data['develop_detail_data']);
        $excel->setFileName("研发情况汇总明细");
        $excel->export();
    }


    /**
     * 产值分析明细
     * @param $data
     * User: czx
     * Date: 2019-01-13 01:14:05
     * Update: 2019-01-13 01:14:05
     * Version: 1.00
     */
    public function exportOutputValueDetailData($data)
    {


        $excel = new PHPExcel();
        // 将附件一的单元格合并
        $excel->mergeCells("A1:A2");
        $excel->setCellValue("A1", "公司名称");
        $header_top = array();
        $header_top_t = array();
        $header_top_h = array();

        foreach ($data as $key => $value) {
            foreach ($value[0] as $key1 => $value1) {
                $header_top_t[] = $value1['title'];
            }
            foreach ($value[1] as $key1 => $value1) {
                $header_top_h[] = $value1['title'];
            }
            break;
        }
        for ($i = 0; $i < count($header_top_t); $i++) {
            $one = array();
            $start = getExcelColumn($i * 13 + 1);
            $end = getExcelColumn($i * 13 + 1 + 4);
            $one['start'] = $start . "1";
            $one['end'] = $end . "1";
            $one['str'] = $header_top_t[$i];
            $header_top[] = $one;

            $two = array();
            $start = getExcelColumn($i * 13 + 1 + 5);
            $end = getExcelColumn($i * 13 + 1 + 9);
            $two['start'] = $start . "1";
            $two['end'] = $end . "1";
            $two['str'] = $header_top_h[$i];
            $header_top[] = $two;

            $three = array();
            $start = getExcelColumn($i * 13 + 1 + 10);
            $end = getExcelColumn($i * 13 + 1 + 12);
            $three['start'] = $start . "1";
            $three['end'] = $end . "1";
            $three['str'] = "同比增长率（%）";
            $header_top[] = $three;
        }


        foreach ($header_top as $key => $value) {
            $excel->mergeCells("{$value['start']}:{$value['end']}");
            $excel->setCellValue("{$value['start']}", $value['str']);
        }

        $excelListData = array();
        foreach ($data as $key => $value) {
            if ($key == "time_period_arr") break;
            $pos = 0;
            $header = array();
            $header[] = '公司名称';
            $excelData = array();
            $excelData['company'] = $value['company_name'];
            for ($i = 0; $i < count($data['time_period_arr']); $i++) {
                $one = $data['time_period_arr'][$i];
                $str = $one[0] . "-" . $one[1];
                foreach ($value as $key1 => $value1) {

                    ++$pos;
                    $excelData['key' . $pos] = $value[0][$str]['income_output_value'];
                    $header[] = '产值';

                    ++$pos;
                    $excelData['key' . $pos] = $value[0][$str]['export_value'];
                    $header[] = '出口额';

                    ++$pos;
                    $excelData['key' . $pos] = $value[0][$str]['investment_value'];
                    $header[] = '投资额';

                    ++$pos;
                    $excelData['key' . $pos] = $value[0][$str]['profit_value'];
                    $header[] = '利润';

                    ++$pos;
                    $excelData['key' . $pos] = $value[0][$str]['taxes_value'];
                    $header[] = '纳税';

                    ++$pos;
                    $excelData['key' . $pos] = $value[1][$str]['income_output_value'];
                    $header[] = '产值';

                    ++$pos;
                    $excelData['key' . $pos] = $value[1][$str]['export_value'];
                    $header[] = '出口额';

                    ++$pos;
                    $excelData['key' . $pos] = $value[1][$str]['investment_value'];
                    $header[] = '投资额';

                    ++$pos;
                    $excelData['key' . $pos] = $value[1][$str]['profit_value'];
                    $header[] = '利润';

                    ++$pos;
                    $excelData['key' . $pos] = $value[1][$str]['taxes_value'];
                    $header[] = '纳税';

                    ++$pos;
                    $excelData['key' . $pos] = $value[2][$str]['income_output_value_rate'];
                    $header[] = '产值';

                    ++$pos;
                    $excelData['key' . $pos] = $value[2][$str]['export_value_rate'];
                    $header[] = '出口额';

                    ++$pos;
                    $excelData['key' . $pos] = $value[2][$str]['investment_value_rate'];
                    $header[] = '投资额';
                    break;
                }
            }
            $excelListData[] = $excelData;
        }
        $i = 0;
        foreach ($header as $value) {
            $excel->setCellValue(getExcelColumn($i) . "2", $value);
            ++$i;
        }
        $excel->setFileName('产值分析明细');
        $excel->setStartRow(3);
        $excel->setStartCol("A");
        $excel->setExportList($excelListData);
        $excel->setCellValueByExportList();
        $endCell = $excel->getEndCol() . $excel->getEndRow();
        $boldCell = $excel->getEndCol() . "2";
        $excel->setBold("A1:{$boldCell}");
        $excel->setVerticalAndHorizontalCenter("A1:{$endCell}");
        $excel->export();

    }

    /**
     * 获取导出产值分析明细数据
     * @param $data
     * @return array
     * User: czx
     * Date: 2019-01-13 15:34:50
     * Update: 2019-01-13 15:34:50
     * Version: 1.00
     */
    public function getExportOutputValueDetail($data, $unit, $start_time, $end_time)
    {
        $t_year = date("Y", $start_time);  //今年的年份
        $y_year = $t_year - 1;     //去年的年份
        $start_mouth = intval(date("m", $start_time));  //开始的月份
        $end_mouth = intval(date("m", $end_time));   //结束的月份
        if ($end_mouth < $start_mouth) return getReturn(-1, "结束月份不能小于开始月份");
        $time_period_arr = $this->getTimePeriodArr($start_mouth, $end_mouth, $unit);
        $exportData = array();
        //数据初始化
        foreach ($data as $key => $value) {
            for ($i = 0; $i < count($time_period_arr); $i++) {
                $one_time_period = $time_period_arr[$i];
                $time_period_str = $one_time_period[0] . "-" . $one_time_period[1];
                $exportData[$value['company_id']][0][$time_period_str]['income_output_value'] = 0;
                $exportData[$value['company_id']][0][$time_period_str]['export_value'] = 0;
                $exportData[$value['company_id']][0][$time_period_str]['investment_value'] = 0;
                $exportData[$value['company_id']][0][$time_period_str]['profit_value'] = 0;
                $exportData[$value['company_id']][0][$time_period_str]['taxes_value'] = 0;

                $exportData[$value['company_id']][1][$time_period_str]['income_output_value'] = 0;
                $exportData[$value['company_id']][1][$time_period_str]['export_value'] = 0;
                $exportData[$value['company_id']][1][$time_period_str]['investment_value'] = 0;
                $exportData[$value['company_id']][1][$time_period_str]['profit_value'] = 0;
                $exportData[$value['company_id']][1][$time_period_str]['taxes_value'] = 0;

                $exportData[$value['company_id']][2][$time_period_str]['income_output_value_rate'] = 0;
                $exportData[$value['company_id']][2][$time_period_str]['export_value_rate'] = 0;
                $exportData[$value['company_id']][2][$time_period_str]['investment_value_rate'] = 0;


                if (empty($unit)) {
                    $ttime_period_title_str = $t_year . "(" . $one_time_period[0] . ")（万元）";
                    $ytime_period_title_str = $y_year . "(" . $one_time_period[0] . ")（万元）";
                } else {
                    $ttime_period_title_str = $t_year . "(" . $one_time_period[0] . "-" . $one_time_period[1] . ")（万元）";
                    $ytime_period_title_str = $y_year . "(" . $one_time_period[0] . "-" . $one_time_period[1] . ")（万元）";
                }

                $exportData[$value['company_id']][0][$time_period_str]['title'] = $ttime_period_title_str;
                $exportData[$value['company_id']][1][$time_period_str]['title'] = $ytime_period_title_str;
                $exportData[$value['company_id']][2][$time_period_str]['title'] = "同比增长率（%）";
            }
        }
        foreach ($data as $key => $value) {
            $one_year = date("Y", $value['apply_time']);
            $one_mouth = intval(date("m", $value['apply_time']));
            $time_period_str = "";
            $time_period_title_str = "";
            for ($i = 0; $i < count($time_period_arr); $i++) {
                $one_time_period = $time_period_arr[$i];
                if ($one_mouth >= $one_time_period[0] && $one_mouth <= $one_time_period[1]) {
                    $time_period_str = $one_time_period[0] . "-" . $one_time_period[1];
                    if (empty($unit)) {
                        $time_period_title_str = $one_year . "(" . $one_mouth . ")（万元）";
                    } else {
                        $time_period_title_str = $one_year . "(" . $one_time_period[0] . "-" . $one_time_period[1] . ")（万元）";
                    }
                }
            }


            $exportData[$value['company_id']]['company_name'] = $value['company_name'];
            $tag = 0;
            if ($one_year != $t_year) $tag = 1;
            if (empty($exportData[$value['company_id']][$tag][$time_period_str]['income_output_value']))
                $exportData[$value['company_id']][$tag][$time_period_str]['income_output_value'] = 0;

            if (empty($exportData[$value['company_id']][$tag][$time_period_str]['export_value']))
                $exportData[$value['company_id']][$tag][$time_period_str]['export_value'] = 0;

            if (empty($exportData[$value['company_id']][$tag][$time_period_str]['investment_value']))
                $exportData[$value['company_id']][$tag][$time_period_str]['investment_value'] = 0;

            if (empty($exportData[$value['company_id']][$tag][$time_period_str]['profit_value']))
                $exportData[$value['company_id']][$tag][$time_period_str]['profit_value'] = 0;

            if (empty($exportData[$value['company_id']][$tag][$time_period_str]['taxes_value']))
                $exportData[$value['company_id']][$tag][$time_period_str]['taxes_value'] = 0;

            $exportData[$value['company_id']][$tag][$time_period_str]['income_output_value'] += $value['income_output_value'];
            $exportData[$value['company_id']][$tag][$time_period_str]['export_value'] += $value['export_value'];
            $exportData[$value['company_id']][$tag][$time_period_str]['investment_value'] += $value['investment_value'];
            $exportData[$value['company_id']][$tag][$time_period_str]['profit_value'] += $value['profit_value'];
            $exportData[$value['company_id']][$tag][$time_period_str]['taxes_value'] += $value['taxes_value'];

            $exportData[$value['company_id']][$tag][$time_period_str]['title'] = $time_period_title_str;

            if (empty($exportData[$value['company_id']][0][$time_period_str]['income_output_value']) ||
                empty($exportData[$value['company_id']][1][$time_period_str]['income_output_value'])) {
                $exportData[$value['company_id']][2][$time_period_str]['income_output_value_rate'] = 0;
            } else {
                $exportData[$value['company_id']][2][$time_period_str]['income_output_value_rate'] =
                    round(($exportData[$value['company_id']][0][$time_period_str]['income_output_value'] -
                            $exportData[$value['company_id']][1][$time_period_str]['income_output_value']) /
                        $exportData[$value['company_id']][1][$time_period_str]['income_output_value'], 4);
            }

            if (empty($exportData[$value['company_id']][0][$time_period_str]['export_value']) ||
                empty($exportData[$value['company_id']][1][$time_period_str]['export_value'])) {
                $exportData[$value['company_id']][2][$time_period_str]['export_value_rate'] = 0;
            } else {
                $exportData[$value['company_id']][2][$time_period_str]['export_value_rate'] =
                    round(($exportData[$value['company_id']][0][$time_period_str]['export_value'] -
                            $exportData[$value['company_id']][1][$time_period_str]['export_value']) /
                        $exportData[$value['company_id']][1][$time_period_str]['export_value'], 4);
            }

            if (empty($exportData[$value['company_id']][0][$time_period_str]['investment_value']) ||
                empty($exportData[$value['company_id']][1][$time_period_str]['investment_value'])) {
                $exportData[$value['company_id']][2][$time_period_str]['investment_value_rate'] = 0;
            } else {
                $exportData[$value['company_id']][2][$time_period_str]['investment_value_rate'] =
                    round(($exportData[$value['company_id']][0][$time_period_str]['investment_value'] -
                            $exportData[$value['company_id']][1][$time_period_str]['investment_value']) /
                        $exportData[$value['company_id']][1][$time_period_str]['investment_value'], 4);
            }

        }

        $exportData['time_period_arr'] = $time_period_arr;
        return getReturn(200, '成功', $exportData);
    }

    /**
     * 获取时间数组
     * @param $start_mouth
     * @param $end_time
     * @return array
     * User: czx
     * Date: 2019-01-13 16:55:31
     * Update: 2019-01-13 16:55:31
     * Version: 1.00
     */
    public function getTimePeriodArr($start_mouth, $end_time, $unit)
    {
        $time_period_arr = array();
        if (empty($unit)) {
            for ($i = $start_mouth; $i <= $end_time; $i++)
                $time_period_arr[] = array($i, $i);
        } else if ($unit == 1) {
            for ($i = $start_mouth; $i <= $end_time; $i++) {
                if ($end_time < ($i + 2)) break;
                $time_period_arr[] = array($i, $i + 2);
                $i = $i + 2;
            }

        } else {
            $time_period_arr[] = array($start_mouth, $end_time);
        }
        return $time_period_arr;
    }

}
