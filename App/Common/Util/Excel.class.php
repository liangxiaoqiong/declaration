<?php

namespace Common\Util;

class Excel extends Base
{

    const MAX_NUM = 5000;

    private $map; // 映射
    private $data; // 原始数据
    private $header; // 标题数组
    private $exportList; // 导出的数据
    private $fileName = ''; // 文件名称
    private $exportListNum; // 导出数据的数量
    private $firstLineNum = 1; // 首行的位置
    private $firstColCh = 'A'; // 首列的位置
    private $title = ''; // 标题
    private $boldLineNum = [1]; // 加粗的行数

    /**
     * Excel constructor.
     * @param array $map
     * @param array $data
     */
    public function __construct($map = [], $data = [])
    {
        if (!empty($map) && !empty($data)) {
            $this->map = $map;
            $this->data = $data;
            $this->buildHeader();
            $this->buildExportList();
        }

    }

    public function setHeader($header)
    {
        $this->header = $header;
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->header;
    }

    public function setExportList($list)
    {
        $this->exportList = $list;
        $this->exportListNum = count($this->exportList);
    }

    /**
     * @return mixed
     */
    public function getExportList()
    {
        return $this->exportList;
    }


    public function setFileName($name)
    {
        $this->fileName = $name;
    }

    public function getFileName()
    {
        if (empty($this->fileName)) {
            return date('Y-m-d');
        }
        return $this->fileName;
    }

    /**
     * @return int
     */
    public function getFirstLineNum()
    {
        return $this->firstLineNum;
    }

    /**
     * @param int $firstLineNum
     */
    public function setFirstLineNum($firstLineNum)
    {
        $this->firstLineNum = $firstLineNum;
    }

    /**
     * @return string
     */
    public function getFirstColCh()
    {
        return $this->firstColCh;
    }

    /**
     * @param string $firstColCh
     */
    public function setFirstColCh($firstColCh)
    {
        $this->firstColCh = $firstColCh;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return array
     */
    public function getBoldLineNum()
    {
        return $this->boldLineNum;
    }

    /**
     * @param array $boldLineNum
     */
    public function setBoldLineNum($boldLineNum)
    {
        $this->boldLineNum = $boldLineNum;
    }

    public function buildHeader()
    {
        foreach ($this->map as $key => $value) {
            $this->header[] = $key;
        }
    }

    public function buildExportList()
    {
        $list = [];
        foreach ($this->data as $data) {
            $item = [];
            foreach ($this->map as $field) {
                $item[$field] = $data[$field];
            }
            $list[] = $item;
        }
        $this->setExportList($list);
    }

    public function isExceedLimit()
    {
        return $this->exportListNum >= self::MAX_NUM;
    }

    /**
     * 导出
     * User: hjun
     * Date: 2018-05-15 15:37:43
     * Update: 2018-05-15 15:37:43
     * Version: 1.00
     */
    public function export()
    {
        if ($this->isExceedLimit()) {
            $this->exportCsv();
        } else {
            $this->exportXls();
        }
    }

    public function exportXls()
    {
        vendor("PHPExcel.PHPExcel");
        $fileName = $this->getFileName() . '.xls';
        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel();
        $objProps = $objPHPExcel->getProperties();

        // region 获取一些数据
        $firstLineNum = $this->getFirstLineNum();
        $firstColCh = $this->getFirstColCh();
        $column = $firstLineNum;
        $header = $this->getHeader();
        $exportList = $this->getExportList();
        $title = $this->getTitle();
        $boldLineNum = $this->getBoldLineNum();
        $key = ord($firstColCh);
        $num = ord($firstColCh);
        // endregion

        // region 设置标题
        if (!empty($title)) {
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . "$column", $title);
            $column++;
        }
        // endregion

        // region 设置表头
        if (!empty($header)) {
            foreach ($this->header as $v) {
                $colum = chr($key);
                if ($key - 65 >= 26) {
                    $key = ord($firstColCh);
                    $colum = chr($num) . chr($key);
                    $num++;
                }
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . "$column", $v);
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . "$column", $v);
                $key += 1;
            }
            $column++;
        }
        // endregion

        // region 设置数据行
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach ($exportList as $key => $rows) { //行写入
            $span = ord($firstColCh);
            $num = ord($firstColCh);
            $start_ch = chr($span);
            foreach ($rows as $keyName => $value) {// 列写入
                $j = chr($span);
                if ($span - 65 >= 26) {
                    $span = ord($firstColCh);
                    $j = chr($num) . chr($span);
                    $num++;
                }
                $objActSheet->setCellValue($j . $column, $value);
                $objActSheet->getColumnDimension($j)->setWidth(25);
                $span++;
            }
            $end_ch = $j;
            $column++;
        }
        // endregion

        // region 设置样式
        // 设置居中
        $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        // 设置文本 换行
        $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);
        // 设置加粗
        if (!empty($boldLineNum)) {
            foreach ($boldLineNum as $num) {
                $objActSheet->getStyle($start_ch . "{$num}:" . $end_ch . "{$num}")->applyFromArray(array('font' => array('bold' => true)));
            }
        }
        // 设置默认选中
        $objPHPExcel->getActiveSheet()->getStyle($firstColCh)->getNumberFormat()->setFormatCode("@");
        // endregion

        // region 导出
        $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
        // endregion
    }

    public function exportCsv()
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 0);
        $fileName = $this->getFileName() . '.csv';
        ob_end_clean();
        ob_start();
        header("Content-Type: text/csv");
        header("Content-Disposition:filename=" . $fileName);
        $fp = fopen('php://output', 'w');
        fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($fp, $this->header);
        $index = 0;
        foreach ($this->exportList as $item) {
            if ($index == 1000) {
                $index = 0;
                ob_flush();
                flush();
            }
            $index++;
            fputcsv($fp, $item);
        }

        ob_flush();
        flush();
        ob_end_clean();
        exit;
    }
}