<?php
/**
 * 公共类
 * Class PublicController
 * @package Manage\Controller
 * User: hjun
 * Date: 2018-03-29 22:57:31
 * Update: 2018-03-29 22:57:31
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */

namespace Manage\Controller;

use Common\Util\DbCache;
use Common\Util\Excel;
use Common\Util\PHPExcel;

class PublicController extends CommonController
{

    public function index()
    {

    }

    //管理员后台主页
    public function adminMain()
    {
        // 首页数据
        $data = [];
        $data['company_apply_meta'] = D('Company')->getAdminIndexMeta();
        $data['data_apply_meta'] = D('DataApply')->getAdminIndexMeta();
        $this->assign('meta', $data);
        $this->display();
    }

    //后台内容主页
    public function main()
    {
        $this->display();
    }

    public function editorMethod()
    {
        $_editor = new \Org\Editor\Ueditor();
    }

    //上传图片

    /**
     * 上传图片
     * @param  integer $img_flag 是否是图片(带缩略图)
     * @return [type]               [description]
     */
    public function upload($img_flag = 0)
    {
        header("Content-Type:text/html; charset=utf-8"); //不然返回中文乱码
        $result = array('state' => '失败', 'url' => '', 'name' => '', 'original' => '');
        $sub_path = I('sfile', '', 'trim,htmlspecialchars'); //判断其他子目录
        $rootPath = I('rootPath', '', 'trim,htmlspecialchars'); //根目录地址
        $img_flag = empty($img_flag) ? 0 : 1;
        /*是否生成子目录*/
        $autoSub_tip = I('autoSub_tip', '');
        $saveName_tip = I('saveName_tip', '');

        if ($autoSub_tip == -1) {
            $autoSub = false;
        } else {
            $autoSub = true;
        }
        $ext = ''; //原文件后缀
        foreach ($_FILES as $key => $v) {
            $strtemp = explode('.', $v['name']);
            $ext = end($strtemp); //获取文件后缀
            break;
        }
        $disallow = array('php', 'asp', 'html', 'htm', 'js', 'aspx', 'jsp', 'shtml', 'xml', 'perl', 'cgi');
        if (!in_array($ext, $disallow)) {

            $yun_upload = new \Common\Lib\YunUpload($img_flag, $sub_path, '', $rootPath, $autoSub, $saveName_tip);
            $upload_result = $yun_upload->upload();
            if ($upload_result['status']) {
                $result['fid'] = $upload_result['fid'];
                $result['state'] = 'SUCCESS';
                $result['info'] = $upload_result['data'];
                $result['url'] = getHostUrl() . $result['info'][0]['url'];
            } else {
                $result['state'] = $upload_result['info'];
            }
        }

        echo json_encode($result);

    }

    //文件/夹管理
    public function browseFile($spath = '', $stype = 'file')
    {
        $base_path = '/uploads/img1';
        $enocdeflag = I('encodeflag', 0, 'intval');
        switch ($stype) {
            case 'picture':
                $base_path = '/uploads/img1';
                break;
            case 'file':
                $base_path = '/uploads/file1';
                break;
            case 'ad':
                $base_path = '/uploads/abc1';
                break;
            default:
                exit('参数错误');
                break;
        }

        if ($enocdeflag) {
            $spath = base64_decode($spath);
        }

        $spath = str_replace('.', '', $spath);
        if (strpos($spath, $base_path) === 0) {
            $spath = substr($spath, strlen($base_path));
        }

        $path = $base_path . '/' . $spath;
        $path = str_replace('//', '/', $path);

        $dir = new \Common\Lib\Dir('.' . $path); //加上.
        $list = $dir->toArray();
        for ($i = 0; $i < count($list); $i++) {

            $list[$i]['isImg'] = 0;
            if ($list[$i]['isFile']) {
                $url = __ROOT__ . rtrim($path, '/') . '/' . $list[$i]['filename'];
                $ext = explode('.', $list[$i]['filename']);
                $ext = end($ext);
                if (in_array($ext, array('jpg', 'png', 'gif'))) {
                    $list[$i]['isImg'] = 1;
                }
            } else {
                //为了兼容URL_MODEL(1、2)
                if (in_array(C('URL_MODEL'), array(1, 2, 3))) {
                    $url = U('Public/browseFile', array('stype' => $stype, 'encodeflag' => 1, 'spath' => base64_encode(rtrim($path, '/') . '/' . $list[$i]['filename'])));
                } else {
                    $url = U('Public/browseFile', array('stype' => $stype, 'spath' => rtrim($path, '/') . '/' . $list[$i]['filename']));
                }

            }
            $list[$i]['url'] = $url;
            $list[$i]['size'] = get_byte($list[$i]['size']);
        }
        //p($list);
        $parentpath = substr($path, 0, strrpos($path, '/'));
        //为了兼容URL_MODEL(1、2)
        if (in_array(C('URL_MODEL'), array(1, 2, 3))) {
            $purl = U('Public/browseFile', array('spath' => base64_encode($parentpath), 'encodeflag' => 1, 'stype' => $stype));
        } else {
            $purl = U('Public/browseFile', array('spath' => $parentpath, 'stype' => $stype));
        }

        $this->assign('purl', $purl);
        $this->assign('vlist', $list);
        $this->assign('stype', $stype);
        $this->assign('type', '浏览文件');
        $this->display();

    }

    public function getDiffDateString()
    {
        $timeStr = getDiffDateString(strtotime($this->req['start']), strtotime($this->req['end']));
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $timeStr));
    }

    public function getNextPeriodDate()
    {
        $endTime = getNextPeriodDate(strtotime($this->req['start']), $this->req['period']);
        $endTime = date('Y-m-d', $endTime - 3600 * 24);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $endTime));
    }

    public function getContractSegmentTotalFee()
    {
        $segment = $this->req;
        $segment['start_time'] = strtotime($segment['start_time']);
        $segment['end_time'] = strtotime($segment['end_time']);
        $monthRental = D('Room')->calculateMonthRental($segment['rent_type'], $segment['rent_value'], $segment['room_area']);
        $result = getContractSegmentTotalFee($segment, $monthRental);
        $total = rentalToRound($result['total']);
        $month = $monthRental;
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', ['total' => $total, 'month' => $month]));
    }

    public function getContractSegmentTotalProperty()
    {
        $segment = $this->req;
        $segment['start_time'] = strtotime($segment['start_time']);
        $segment['end_time'] = strtotime($segment['end_time']);
        $monthRental = D('Room')->calculateMonthProperty($segment['property_type'], $segment['property_value'], $segment['room_area']);
        $result = getContractSegmentTotalFee($segment, $monthRental);
        $total = propertyToRound($result['total']);
        $month = $monthRental;
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', ['total' => $total, 'month' => $month]));
    }

    public function printHtml()
    {
        $html = htmlspecialchars_decode($this->req['html']);
        $key = md5($html);
        $url = D('Contract')->Html2Word($html, "bill{$key}", $this->req['bill_id'], 1);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $url));
    }

    public function canRelet()
    {
        $result = D('Contract')->canRelet($this->req['contract_id'], $this->admin['garden_id']);
        if ($result) {
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success'));
        } else {
            $this->apiResponse(getReturn(CODE_ERROR, '您有未处理完账单,暂时无法续租'));
        }
    }

    public function roles()
    {
        $list = M('role')->where(['garden_id' => $this->admin['garden_id']])->select();
        $list = empty($list) ? [] : $list;
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $list));
    }

    public function access()
    {
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
        $node = M('node')->where($where)->order('sort')->select();
        $node = node2layer($node, $access);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $node));
    }

    public function roleInfo()
    {
        $role = M('role')->find($this->req['id']);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $role));
    }

    /**
     * 获取出租方列表
     * User: hjun
     * Date: 2018-07-12 16:43:40
     * Update: 2018-07-12 16:43:40
     * Version: 1.00
     */
    public function getLessorList()
    {
        $list = D('Lessor')->getList($this->admin['garden_id']);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $list));
    }

    public function getLessor()
    {
        $info = D('Lessor')->getLessor($this->admin['garden_id'], $this->req['lessor_id']);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $info));
    }

    /**
     * 打印账单
     * User: hjun
     * Date: 2018-09-04 10:21:09
     * Update: 2018-09-04 10:21:09
     * Version: 1.00
     */
    public function printBills()
    {
        $contractId = $this->req['contract_id'];
        $value = $this->req['checked'];
        $value = explode(',', $value);
        $contract = D('Contract')->getContract($this->admin['garden_id'], $contractId);
        $bills = D('Room')->getRoomBills($contract['garden_id'], $contract['room_id']);
        $print = [];
        foreach ($value as $type) {
            if (!empty($bills[$type])) {
                $print = array_merge($print, $bills[$type]);
            }
        }
        $total = 0;
        foreach ($print as $key => &$bill) {
            $bill['index'] = $key + 1;
            $bill['period'] = "{$bill['start_time_str']} - {$bill['end_time_str']}";
            $bill['fee_items_name'] = [];
            if (count($bill['fee_items']) > 1) {
                foreach ($bill['fee_items'] as $item) {
                    $bill['fee_items_name'][] = "{$item['item_name']}￥{$item['fee']}元";
                }
                $bill['fee_items_name'] = implode("\r\n", $bill['fee_items_name']);
            } else {
                $bill['fee_items_name'] = "{$bill['fee_items'][0]['item_name']}";
            }

            $bill['total_fee_str'] = '' . round($bill['total_fee'], 2);
            $total += $bill['total_fee_str'];
        }
        $map = [
            '序号' => 'index',
            '账单周期' => 'period',
            '收费项' => 'fee_items_name',
            '总额(元)' => 'total_fee_str',
        ];
        $excel = new PHPExcel($map, $print);
        // 重新组装导出的列表数据
        $list = $excel->getExportList();
        $header = $excel->getHeader();
        // 获取各个起始点
        $startCol = $excel->getStartCol(); // 整个表格的开始列
        $endCol = $excel->incAZ($startCol, count($header) - 1); // 整个表格的结束列
        $startRow = $excel->getStartRow(); // 整个表格的开始行
        $listStartRow = (int)$startRow + 2; // 数据的起始行
        $listEndRow = $listStartRow + count($list) - 1; // 数据的结束行
        $tableStartRow = $listStartRow - 1; // 表格的起始行
        $tableEndRow = $listEndRow + 1; // 表格的结束行
        $endRow = $tableEndRow; // 整个表格的结束行
        $secondCol = $excel->incAZ($startCol);
        $thirdCol = $excel->incAZ($secondCol);
        array_unshift($list, $header);
        array_unshift($list, ["附件一：{$print[0]['room_name']}账单明细"]);
        array_push($list, ['', '合计', '', "=SUM({$endCol}{$listStartRow}:{$endCol}{$listEndRow})"]);
        $excel->setFileName("附件一：{$print[0]['room_name']}账单明细");
        $excel->setExportList($list);
        $excel->setCellValueByExportList();

        // 设置样式
        $excel->setDefaultFontName('Calibri');
        // 设置表格边框
        $excel->setOutlineAndInsideBorders("{$startCol}{$startRow}:{$endCol}{$endRow}");
        // 设置标题字体加粗
        $excel->setBold("{$startCol}{$tableStartRow}:{$endCol}{$tableStartRow}");
        // 设置附件一的单元格以及第一列第二列的单元格都水平、垂直都居中; 第三第四列的垂直居中即可
        $excel->setVerticalAndHorizontalCenter("{$startCol}{$startRow}:{$endCol}{$startRow}");
        $excel->setVerticalAndHorizontalCenter("{$startCol}{$tableStartRow}:{$secondCol}{$tableEndRow}");
        $excel->setVertical("{$thirdCol}{$tableStartRow}:{$endCol}{$tableEndRow}");
        // 将附件一的单元格合并
        $excel->mergeCells("{$startCol}{$startRow}:{$endCol}{$startRow}");
        $excel->setRowHeight($startRow, 43.2);
        // 设置第一列和第二列的宽度、其他列自动适应
        $excel->setColWidth($startCol, 8.11);
        $excel->setColWidth($secondCol, 25);
        $excel->setColWidth($thirdCol, 25);
        $excel->setColWidth($endCol, 15);
        // 设置换行
        $excel->setWrapText("{$thirdCol}{$tableStartRow}:{$thirdCol}{$listEndRow}");
        $excel->export();
    }

    /**
     * 获取主房间列表
     * User: hjun
     * Date: 2018-09-05 14:10:54
     * Update: 2018-09-05 14:10:54
     * Version: 1.00
     */
    public function getMainRoomsToMerge()
    {
        $list = D('Room')->getMainRooms($this->req['floor_id']);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $list));
    }

    /**
     * 获取可以被合并（作为子房间）的房间
     * User: hjun
     * Date: 2018-09-05 14:12:28
     * Update: 2018-09-05 14:12:28
     * Version: 1.00
     */
    public function getCanMergeRoomsToBeMerged()
    {
        $list = D('Room')->getCanMergeRooms($this->req['room_id']);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $list));
    }

    /**
     * 上传图片
     * User: hjun
     * Date: 2018-11-21 14:10:32
     * Update: 2018-11-21 14:10:32
     * Version: 1.00
     */
    public function uploadImage()
    {
        header("Content-Type:text/html; charset=utf-8"); //不然返回中文乱码
        $result = array('state' => '失败', 'url' => '', 'name' => '', 'original' => '');
        $sub_path = I('sfile', '', 'trim,htmlspecialchars'); //判断其他子目录
        $rootPath = I('rootPath', '', 'trim,htmlspecialchars'); //根目录地址
        $img_flag = 1;
        /*是否生成子目录*/
        $autoSub_tip = I('autoSub_tip', '');
        $saveName_tip = I('saveName_tip', '');

        if ($autoSub_tip == -1) {
            $autoSub = false;
        } else {
            $autoSub = true;
        }
        $ext = ''; //原文件后缀
        foreach ($_FILES as $key => $v) {
            $strtemp = explode('.', $v['name']);
            $ext = end($strtemp); //获取文件后缀
            break;
        }
        $allow = ['jpg', 'jpeg', 'png'];
        if (in_array($ext, $allow)) {
            $yun_upload = new \Common\Lib\YunUpload($img_flag, $sub_path, '', $rootPath, $autoSub, $saveName_tip);
            $yun_upload->setMaxSize(5 * 1024 * 1024);
            $upload_result = $yun_upload->upload();
            if ($upload_result['status']) {
                $result['fid'] = $upload_result['fid'];
                $result['state'] = 'SUCCESS';
                $result['info'] = $upload_result['data'][0];
                $result['info']['size_kb'] = round($result['info']['size'] / 1024, 1) . 'K';
                $result['url'] = getHostUrl() . $result['info']['url'];
            } else {
                $result['state'] = $upload_result['info'];
            }
        } else {
            $result['state'] = "图片类型不允许";
        }
        $this->apiResponse($result);
    }

    /**
     * 上传附件
     * User: hjun
     * Date: 2018-11-21 14:10:32
     * Update: 2018-11-21 14:10:32
     * Version: 1.00
     */
    public function uploadAttachment()
    {
        header("Content-Type:text/html; charset=utf-8"); //不然返回中文乱码
        $result = array('state' => 'ERROR', 'url' => '', 'name' => '', 'original' => '');
        $sub_path = 'attachment';
        $rootPath = '';
        $img_flag = 0;
        /*是否生成子目录*/
        $autoSub_tip = I('autoSub_tip', '');
        $saveName_tip = I('saveName_tip', '');

        if ($autoSub_tip == -1) {
            $autoSub = false;
        } else {
            $autoSub = true;
        }
        $ext = ''; //原文件后缀
        foreach ($_FILES as $key => $v) {
            $strtemp = explode('.', $v['name']);
            $ext = end($strtemp); //获取文件后缀
            break;
        }
        $allow = ['xls', 'doc', 'docx', 'ppt', 'zip', 'rar'];
        if (in_array($ext, $allow)) {
            $yun_upload = new \Common\Lib\YunUpload($img_flag, $sub_path, '', $rootPath, $autoSub, $saveName_tip);
            $yun_upload->setMaxSize(10 * 1024 * 1024);
            $upload_result = $yun_upload->upload();
            if ($upload_result['status']) {
                $result['fid'] = $upload_result['fid'];
                $result['state'] = 'SUCCESS';
                $result['msg'] = '上传成功';
                $result['info'] = $upload_result['data'][0];
                $result['info']['size_kb'] = round($result['info']['size'] / 1024, 1) . 'K';
                $result['url'] = getHostUrl() . $result['info']['url'];
            } else {
                $result['state'] = $upload_result['info'];
                $result['msg'] = $upload_result['info'];
            }
        } else {
            $result['state'] = "附件类型不允许";
            $result['msg'] = "附件类型不允许";
        }
        $this->apiResponse($result);
    }

    /**
     * 客户资料添加与更新 前信息返回
     * User: czx
     * Date: 2018/5/14 10:25:18
     * Update: 2018/5/14 10:25:18
     * Version: 1.00
     */
    public function addCustomerBefore()
    {
        if (empty($this->admin['garden_id'])) {
            $this->apiResponse(getReturn(CODE_ERROR, "园区编号不能为空"));
        }
        $returnData = [];
        $returnData['roomList'] = D('Room')->getSelectRoomList($this->admin['garden_id']);
        $returnData['adminList'] = D('Admin')->getSelectList($this->admin['garden_id']);
        $returnData['customerInfo'] = '';
        $customer_id = $this->req['customer_id'];
        if (!empty($customer_id)) {
            $returnInfoData = D("Customer")->getCustomer($customer_id);
            $returnData['customerInfo'] = $returnInfoData['data'];
        }
        $this->apiResponse(getReturn(CODE_SUCCESS, "success", $returnData));
    }

    /**
     * 获取客户详情
     * User: hjun
     * Date: 2018-12-28 12:54:16
     * Update: 2018-12-28 12:54:16
     * Version: 1.00
     */
    public function getCustomerItem()
    {
        if (IS_AJAX) {
            if (empty($this->admin['garden_id'])) {
                $this->apiResponse(getReturn(CODE_ERROR, "园区编号不能为空"));
            }
            if (empty($this->req['customer_id'])) {
                return getReturn(-1, "客户编号不能为空");
            }
            $customerItem = D("Customer")->getCustomerItem($this->req);
            $customerList = D("CustomerLog")->getCustomerLogList($this->req);
            $returnData = [];
            $returnData['customerItem'] = $customerItem['data'];
            $returnData['customerList'] = $customerList['data'];
            $this->apiResponse(getReturn(200, "success", $returnData));
        }
        $this->display();
    }

    /**
     * 获取杂项数据
     * User: hjun
     * Date: 2018-04-18 17:43:56
     * Update: 2018-04-18 17:43:56
     * Version: 1.00
     */
    public function getSundryData()
    {
        $data = D('Contract')->getSundryDataForActionData();
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
    }

    /**
     * 获取左侧楼宇列表
     * User: hjun
     * Date: 2018-04-12 17:35:55
     * Update: 2018-04-12 17:35:55
     * Version: 1.00
     */
    public function getLeftData()
    {
        $data = D('Building')->getLeftData($this->admin['garden_id']);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
    }

    /**
     * 获取右侧数据
     * User: hjun
     * Date: 2018-04-12 17:41:59
     * Update: 2018-04-12 17:41:59
     * Version: 1.00
     */
    public function getRightData()
    {
        $where = [];
        $options = [];
        // 房间面积
        $result = getRangeWhere($this->req, 'room_area_min', 'room_area_max', '房间面积范围不正确');
        if ($result['code'] !== CODE_SUCCESS) $this->apiResponse($result);
        $areaWhere = $result['data'];
        if (!empty($areaWhere)) $where['b.room_area'] = $areaWhere;
        // 房间租金
        $result = getRangeWhere($this->req, 'month_rental_min', 'month_rental_max', '房间租金范围不正确');
        if ($result['code'] !== CODE_SUCCESS) $this->apiResponse($result);
        $monthRental = $result['data'];
        if (!empty($monthRental)) $where['b.month_rental'] = $monthRental;
        // 模糊搜索
        $field = [
            'b.remark' => 'remark', // 装修备注
            'b.room_name' => 'room_name', // 房间号
            'b.customer_name' => 'customer_name', // 租客名
            'b.customer_mobile' => 'customer_mobile', // 手机号
        ];
        foreach ($field as $key => $value) {
            if (!empty($this->req[$value])) {
                $where[$key] = ['like', "%{$this->req[$value]}%"];
            }
        }

        // 出租方
        if (!empty($this->req['lessor_id'])) {
            $where['b.lessor_id'] = $this->req['lessor_id'];
        }

        $options['no_status_where'] = $where;
        // 状态 0-全部 1-已租 2-空置 3-暂停 4-收租 5-到期
        switch ((int)$this->req['status']) {
            case 1:
                $where['b.rent_status'] = 1;
                break;
            case 2:
                $where['b.room_status'] = 1;
                $where['b.rent_status'] = 0;
                break;
            case 3:
                $where['b.room_status'] = 0;
                $where['b.rent_status'] = 0;
                break;
            case 4:
                $where['b.next_rental_time'] = ['gt', 0];
                break;
            case 5:
                $now = date('Y-m-d');
                $now = strtotime($now);
                $where['b.rent_status'] = 1;
                $where['b.contract_end_time'] = ['elt', $now];
                break;
            default:
                break;
        }
        $options['cache'] = true;
        $options['where'] = $where;
        $result = D('Building')->getRightData($this->admin['garden_id'], $this->req['building_id'], $options);
        if ($this->req['action'] == 'query') {
            $this->apiResponse($result);
        } elseif ($this->req['action'] == 'export') {
            $list = $result['data']['list'];
            $list = getExportRightData($list);
            $map = [
                BUILDING_NAME => 'building_name',
                '楼层' => 'floor_name',
                '房间ID' => 'room_id',
                '房间号' => 'room_name',
                '出租方' => 'lessor_name',
                '面积' => 'room_area',
                '月租' => 'month_rental',
                '月管理费' => 'month_property',
                '状态' => 'room_status_name',
                '租客名' => 'customer_name',
                '起租日期' => 'contract_start_time_string',
                '合同到期日' => 'contract_end_time_string',
                '下期收租日' => 'next_rental_time_string',
            ];
            $excel = new Excel($map, $list);
            $excel->export();
        }
    }

    /**
     * 获取楼宇详情
     * User: hjun
     * Date: 2018-04-16 18:33:51
     * Update: 2018-04-16 18:33:51
     * Version: 1.00
     */
    public function getBuildingDetail()
    {
        $info = D('Building')->getBuildingDetail($this->admin['garden_id'], $this->req['building_id']);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $info));
    }

    /**
     * 获取楼层数据
     * User: hjun
     * Date: 2018-04-27 18:13:15
     * Update: 2018-04-27 18:13:15
     * Version: 1.00
     */
    public function getFloor()
    {
        $floor = D('Floor')->getFloor($this->admin['garden_id'], $this->req['floor_id']);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $floor));
    }

    /**
     * 签约之前获取房间的基础信息
     * User: hjun
     * Date: 2018-04-19 11:06:39
     * Update: 2018-04-19 11:06:39
     * Version: 1.00
     */
    public function getRoomInfoBeforeSign()
    {
        $result = D('Room')->getInfoBeforeSign($this->admin['garden_id'], $this->req['room_id']);
        $this->apiResponse($result);
    }

    /**
     * 获取合同操作页面的数据
     * User: hjun
     * Date: 2018-08-26 15:05:59
     * Update: 2018-08-26 15:05:59
     * Version: 1.00
     */
    public function getContractActionData()
    {
        $model = D('Contract');
        $model->setGardenId($this->admin['garden_id']);
        $data = $model->getActionData($this->req['room_id'], $this->req['type']);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
    }

    /**
     * 获取合同数据
     * User: hjun
     * Date: 2018-05-07 17:03:29
     * Update: 2018-05-07 17:03:29
     * Version: 1.00
     */
    public function getContract()
    {
        $info = D('Contract')->getContractAndTransform($this->admin['garden_id'], $this->req['contract_id']);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $info));
    }

    /**
     * 预览合同
     * User: hjun
     * Date: 2018-05-15 07:16:58
     * Update: 2018-05-15 07:16:58
     * Version: 1.00
     */
    public function previewContract()
    {
        if (IS_POST) {
            $result = D('Contract')->preview($this->admin['garden_id'], $this->req);
            $this->apiResponse($result);
        }
        $this->display('Contract/contractPreview');
        exit('error');
    }

    /**
     * 公摊账单列表
     * User: hjun
     * Date: 2018-05-22 01:33:47
     * Update: 2018-05-22 01:33:47
     * Version: 1.00
     */
    public function getPublicBillList()
    {
        $where = [];
        // 收取状态 all_no-全部未收 all_do-全部已收 some-部分未收
        if (!empty($this->req['status'])) {
            switch ($this->req['status']) {
                case 'all_no':
                    $where['a.actual_pay_money'] = 0;
                    break;
                case 'all_do':
                    $where['a.actual_pay_money'] = ['exp', '= total_pay_money'];
                    break;
                case 'some':
                    $where['a.actual_pay_money'] = [['exp', '< total_pay_money'], ['gt', 0]];
                    break;
                default:
                    break;
            }
        }
        $page = $this->req['page'] > 0 ? $this->req['page'] : 1;
        $limit = $this->req['limit'] > 0 ? $this->req['limit'] : 20;
        $result = D('PublicBill')->getIndexList($this->admin['garden_id'], $page, $limit, $where);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $result));
    }

    /**
     * 获取选择的列表
     * User: hjun
     * Date: 2018-05-23 09:17:25
     * Update: 2018-05-23 09:17:25
     * Version: 1.00
     */
    public function getPublicBillSelectList()
    {
        $data = D('Room')->getTreeList($this->admin['garden_id'], $this->req);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
    }

    /**
     * 获取公摊信息
     * User: hjun
     * Date: 2018-05-22 09:33:26
     * Update: 2018-05-22 09:33:26
     * Version: 1.00
     */
    public function getPublicBillInfo()
    {
        $info = D('PublicBill')->getInfo();
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $info));
    }

    /**
     * 获取账单详细数据
     * User: hjun
     * Date: 2018-05-16 02:49:38
     * Update: 2018-05-16 02:49:38
     * Version: 1.00
     */
    public function getBillDetail()
    {
        $info = D('Bill')->getBillDetail($this->admin['garden_id'], $this->req['bill_id']);
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $info));
    }

    /**
     * 收租时获取账单
     * User: hjun
     * Date: 2018-05-14 10:18:28
     * Update: 2018-05-14 10:18:28
     * Version: 1.00
     */
    public function getBillBeforeCollect()
    {
        $result = D('Bill')->getBillBeforeCollect($this->admin['garden_id'], $this->req['bill_id']);
        $this->apiResponse($result);
    }

    /**
     * 打印账单页面
     * User: hjun
     * Date: 2018-12-29 15:06:16
     * Update: 2018-12-29 15:06:16
     * Version: 1.00
     */
    public function billPrint()
    {
        $this->display('Bill/billPrint');
    }

    /**
     * 公摊选择房间页面
     * User: hjun
     * Date: 2018-12-29 15:09:31
     * Update: 2018-12-29 15:09:31
     * Version: 1.00
     */
    public function publicBillRoom()
    {
        $this->display('PublicBill/publicBillRoom');
    }

    /**
     * 合同打印预览
     * User: hjun
     * Date: 2018-12-29 15:11:20
     * Update: 2018-12-29 15:11:20
     * Version: 1.00
     */
    public function contractPrint()
    {
        $contract_id = I('contract_id');
        $info = D('Contract')->getContract($this->admin['garden_id'], $contract_id);

        $info['pictures_url'] = json_decode($info['contract_pictures'], true);
        $info['idcard_pictures_url'] = json_decode($info['customer_id_pictures'], true);
        $info['charging_array'] = json_decode($info['charging_info'], true);
        $this->assign('data', $info);
        $this->display('Contract/contractPrint');
    }

    /**
     * 获取楼宇列表选择数据
     * User: hjun
     * Date: 2018-12-29 16:03:44
     * Update: 2018-12-29 16:03:44
     * Version: 1.00
     */
    public function getBuildingList()
    {
        if (empty($this->admin['garden_id'])) {
            $this->apiResponse(getReturn(CODE_ERROR, "园区编号不能为空"));
        }
        $returnData = D('Building')->getSelectList($this->admin['garden_id']);
        $this->apiResponse(getReturn(200, "success", $returnData));
    }

    /**
     * 获取资讯数据
     * User: hjun
     * Date: 2018-11-14 10:53:51
     * Update: 2018-11-14 10:53:51
     * Version: 1.00
     */
    public function getNews()
    {
        $data = D('News')->getNews($this->req['news_id']);
        if (empty($data)) {
            $this->apiResponse(getReturn(CODE_ERROR, '资讯不存在'));
        }
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
    }

    /**
     * 显示签约页面
     * User: hjun
     * Date: 2019-01-04 02:47:46
     * Update: 2019-01-04 02:47:46
     * Version: 1.00
     */
    public function showContractAction()
    {
        if (canDraftContract() || canAuditContract() || canConfirmContract() || canRejectContract()) {
            $this->assign('type', 0);
            $this->display('Contract/contract/action');
        }
        exit('<h1>没有权限</h1><p><a href="javascript:history.go(-1);">返回</a></p>');
    }

    /**
     * 清楚缓存
     * @param bool $dellog
     * User: hjun
     * Date: 2019-01-12 16:10:56
     * Update: 2019-01-12 16:10:56
     * Version: 1.00
     */
    public function clearCache($dellog = false)
    {
        header("Content-Type:text/html; charset=utf-8"); //不然返回中文乱码

        //清除缓存
        is_dir(DATA_PATH . '_fields/') && del_dir_file(DATA_PATH . '_fields/', false);
        is_dir(CACHE_PATH) && del_dir_file(CACHE_PATH, false); //模板缓存（混编后的）
        is_dir(DATA_PATH) && del_dir_file(DATA_PATH, false); //项目数据（当使用快速缓存函数F的时候，缓存的数据）
        is_dir(TEMP_PATH) && del_dir_file(TEMP_PATH, false); //项目缓存（当S方法缓存类型为File的时候，这里每个文件存放的就是缓存的数据）
        if ($dellog) {
            is_dir(LOG_PATH) && del_dir_file(LOG_PATH, false); //日志
        }
        is_file(RUNTIME_PATH . APP_MODE . '~runtime.php') && @unlink(RUNTIME_PATH . APP_MODE . '~runtime.php'); //RUNTIME_FILE
        $dbCache = new DbCache();
        $dbCache->clear();
        $this->success('清除完成！');
        //echo '清除完成';
    }

    /**
     * 获取选择项数据
     * User: hjun
     * Date: 2019-01-12 20:07:02
     * Update: 2019-01-12 20:07:02
     * Version: 1.00
     */
    public function getSelectData()
    {
        $data = [];
        $data['company_form'] = D('CompanyForm')->getSelectList();
        $data['capital_form'] = D('CapitalForm')->getSelectList();
        $data['apply_fields'] = D('ApplyFields')->getSelectList();
        $data['nav_list'] = D('Nav')->getSelectList();
        $data['member_title_list'] = D('MemberTitle')->getSelectList();
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
    }

    /**
     * 获取一个开启的问卷
     * User: hjun
     * Date: 2019-01-17 02:18:39
     * Update: 2019-01-17 02:18:39
     * Version: 1.00
     */
    public function getTodoQnr()
    {
        if ($this->admin['usertype'] == SYSTEM_ADMIN) {
            $info = [];
        } else {
            $where = [];
            $where['is_open'] = QNR_OPEN_YES;
            $where['is_delete'] = NOT_DELETED;
            $info = D('Qnr')->field('qnr_id')->where($where)->find();
            // 查看是否有回答过
            if (!empty($info)) {
                $where = [];
                $where['qnr_id'] = $info['qnr_id'];
                $where['is_delete'] = NOT_DELETED;
                $where['company_id'] = $this->admin['company_id'];
                $answer = D('QnrAnswer')->where($where)->find();
                if (!empty($answer)) {
                    $info = [];
                } else {
                    $info = D('Qnr')->getQnr($info['qnr_id']);
                }
            } else {
                $info = [];
            }
        }
        $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $info));
    }

    /**
     * 更新权限
     * User: hjun
     * Date: 2019-01-19 01:56:23
     * Update: 2019-01-19 01:56:23
     * Version: 1.00
     */
    public function syncAuth()
    {
        \Org\Util\Rbac::saveAccessList(); //静态方法，读取权限放到session
        $this->apiResponse(getReturn(CODE_SUCCESS));
    }
}
