<?php

namespace Common\Util;

vendor("PHPExcel.PHPExcel");

class PHPExcel extends Excel
{

    const EXCEL_5 = 'Excel5';
    const EXCEL_2007 = 'Excel2007';

    const VERTICAL_CENTER = \PHPExcel_Style_Alignment::VERTICAL_CENTER;
    const HORIZONTAL_CENTER = \PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
    const BORDER_THICK = \PHPExcel_Style_Border::BORDER_THICK;
    const BORDER_NONE = \PHPExcel_Style_Border::BORDER_NONE;
    const BORDER_DASHDOT = \PHPExcel_Style_Border::BORDER_DASHDOT;
    const BORDER_DASHDOTDOT = \PHPExcel_Style_Border::BORDER_DASHDOTDOT;
    const BORDER_DASHED = \PHPExcel_Style_Border::BORDER_DASHED;
    const BORDER_DOTTED = \PHPExcel_Style_Border::BORDER_DOTTED;
    const BORDER_DOUBLE = \PHPExcel_Style_Border::BORDER_DOUBLE;
    const BORDER_HAIR = \PHPExcel_Style_Border::BORDER_HAIR;
    const BORDER_MEDIUM = \PHPExcel_Style_Border::BORDER_MEDIUM;
    const BORDER_MEDIUMDASHDOT = \PHPExcel_Style_Border::BORDER_MEDIUMDASHDOT;
    const BORDER_MEDIUMDASHDOTDOT = \PHPExcel_Style_Border::BORDER_MEDIUMDASHDOTDOT;
    const BORDER_MEDIUMDASHED = \PHPExcel_Style_Border::BORDER_MEDIUMDASHED;
    const BORDER_SLANTDASHDOT = \PHPExcel_Style_Border::BORDER_SLANTDASHDOT;
    const BORDER_THIN = \PHPExcel_Style_Border::BORDER_THIN;

    /**
     * @var \PHPExcel
     */
    private $objPHPExcel;

    /**
     * @var \PHPExcel_Worksheet
     */
    private $objWorkSheet;

    /**
     * @var \PHPExcel_Writer_IWriter
     */
    private $objWriter;

    /**
     * @var \PHPExcel_Reader_IReader
     */
    private $objReader;

    private $tableList = [];
    private $writerType = self::EXCEL_5;
    private $readerType = self::EXCEL_5;
    private $startCol = 'A';
    private $startRow = '1';
    private $endCol = '';
    private $endRow = '';

    /**
     * @return \PHPExcel
     */
    public function getObjPHPExcel()
    {
        return $this->objPHPExcel;
    }

    /**
     * @return \PHPExcel_Worksheet
     */
    public function getObjWorkSheet()
    {
        return $this->objWorkSheet;
    }

    /**
     * @return \PHPExcel_Writer_IWriter
     */
    public function getObjWriter()
    {
        return $this->objWriter;
    }

    /**
     * @return \PHPExcel_Reader_IReader
     */
    public function getObjReader()
    {
        return $this->objReader;
    }

    /**
     * @return array
     */
    public function getTableList()
    {
        return $this->tableList;
    }

    /**
     * @param array $tableList
     */
    public function setTableList($tableList)
    {
        $this->tableList = $tableList;
    }

    /**
     * @return string
     */
    public function getStartCol()
    {
        return $this->startCol;
    }

    /**
     * @param string $startCol
     * @return $this
     */
    public function setStartCol($startCol)
    {
        $this->startCol = $startCol;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStartRow()
    {
        return $this->startRow;
    }

    /**
     * @param string $startRow
     * @return $this
     */
    public function setStartRow($startRow)
    {
        $this->startRow = $startRow;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndCol()
    {
        return $this->endCol;
    }

    /**
     * @param string $endCol
     * @return $this
     */
    public function setEndCol($endCol)
    {
        $this->endCol = $endCol;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndRow()
    {
        return $this->endRow;
    }

    /**
     * @param string $endRow
     * @return $this
     */
    public function setEndRow($endRow)
    {
        $this->endRow = $endRow;
        return $this;
    }

    /**
     * @return string
     */
    public function getWriterType()
    {
        return $this->writerType;
    }

    /**
     * @param string $writerType
     * @return \PHPExcel_Writer_IWriter
     */
    public function setWriterType($writerType)
    {
        if (!in_array($writerType, [self::EXCEL_5, self::EXCEL_2007])) {
            $this->writerType = self::EXCEL_5;
        } else {
            $this->writerType = $writerType;
        }
        $this->objWriter = \PHPExcel_IOFactory::createWriter($this->getObjPHPExcel(), $writerType);
        return $this->objWriter;
    }

    /**
     * @return string
     */
    public function getReaderType()
    {
        return $this->readerType;
    }

    /**
     * @param string $readerType
     * @return \PHPExcel_Reader_IReader
     */
    public function setReaderType($readerType)
    {
        if (!in_array($readerType, [self::EXCEL_5, self::EXCEL_2007])) {
            $this->readerType = self::EXCEL_5;
        } else {
            $this->readerType = $readerType;
        }
        $this->objReader = \PHPExcel_IOFactory::createReader($this->getObjPHPExcel(), $readerType);
        return $this->objReader;
    }

    public function getFileName()
    {
        $fileName = parent::getFileName();
        $ext = '.xls';
        if ($this->getWriterType() === self::EXCEL_2007) {
            $ext = '.xlsx';
        }
        return "{$fileName}{$ext}";
    }

    public function __construct(array $map = [], array $data = [])
    {
        parent::__construct($map, $data);
        $header = $this->getHeader();
        $list = $this->getExportList();
        array_unshift($list, $header);
        $this->setTableList($list);
        $this->objPHPExcel = new \PHPExcel();
        $this->objWorkSheet = $this->objPHPExcel->setActiveSheetIndex(0);
        $this->objWriter = \PHPExcel_IOFactory::createWriter($this->objPHPExcel, $this->getWriterType());
    }

    public function incAZ($str, $num = 1)
    {
        $str = strtoupper($str);
        $str = ord($str);
        return getExcelColumn($str - 65 + $num);
    }


    public function decAZ($str, $num = 1)
    {
        $str = strtoupper($str);
        $str = ord($str);
        return getExcelColumn($str - 65 - $num);
    }

    public function setCellValueByExportList($list = [])
    {
        if (empty($list)) {
            $list = $this->getExportList();
        }
        $startCol = $this->getStartCol();
        $startRow = $this->getStartRow();
        $nextRow = $startRow;
        $nextCol = $startCol;

        foreach ($list as $row) {
            $nextCol = $startCol;
            $nextNum = ord($startCol) - 65;
            foreach ($row as $value) {
                $this->objWorkSheet->setCellValue("{$nextCol}{$nextRow}", $value);
                // $nextCol = $this->incAZ($nextCol);
                $nextCol = getExcelColumn(++$nextNum);
            }
            $nextRow++;
        }
        $this->setEndCol($nextCol);
        $this->setEndRow(--$nextRow);
    }

    public function mergeCells($pCellCoordinate = 'A1:A2')
    {
        $sheet = $this->getObjWorkSheet();
        $sheet->mergeCells($pCellCoordinate);
    }

    public function setRowHeight($row = 1, $pValue = -1)
    {
        $sheet = $this->getObjWorkSheet();
        $sheet->getRowDimension($row)->setRowHeight($pValue);
    }

    public function setColWidth($col = 'A', $pValue = -1)
    {
        $sheet = $this->getObjWorkSheet();
        $sheet->getColumnDimension($col)->setWidth($pValue);
    }

    public function setColAutoSize($col = 'A')
    {
        $sheet = $this->getObjWorkSheet();
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    public function setAllBorders($pCellCoordinate = 'A1', $options = [])
    {
        $sheet = $this->getObjWorkSheet();
        if (empty($options)) {
            $options = array(
                'style' => self::BORDER_MEDIUM,
                'color' => array('argb' => '00000000'),
            );
        }
        $styleArray = array(
            'borders' => array(
                'allborders' => $options,
            ),
        );
        $sheet->getStyle($pCellCoordinate)->applyFromArray($styleArray);
    }

    public function setOutlineAndInsideBorders($pCellCoordinate = 'A1', $options = [])
    {
        $sheet = $this->getObjWorkSheet();
        if (empty($options)) {
            $options = [
                'outline' => [
                    'style' => self::BORDER_THICK,
                    'color' => ['argb' => '00000000'],
                ],
                'inside' => [
                    'style' => self::BORDER_MEDIUM,
                    'color' => ['argb' => '00000000'],
                ]
            ];
        }
        $styleArray = array(
            'borders' => array(
                'outline' => $options['outline'],
                'inside' => $options['inside'],
            ),
        );
        $sheet->getStyle($pCellCoordinate)->applyFromArray($styleArray);
    }

    public function setVertical($pCellCoordinate = 'A1', $pValue = self::VERTICAL_CENTER)
    {
        $sheet = $this->getObjWorkSheet();
        $sheet->getStyle($pCellCoordinate)->getAlignment()->setVertical($pValue);
    }

    public function setDefaultHorizontal($pValue = self::HORIZONTAL_CENTER)
    {
        $sheet = $this->getObjWorkSheet();
        $sheet->getDefaultStyle()->getAlignment()->setHorizontal($pValue);
    }

    public function setDefaultVertical($pValue = self::VERTICAL_CENTER)
    {
        $sheet = $this->getObjWorkSheet();
        $sheet->getDefaultStyle()->getAlignment()->setVertical($pValue);
    }

    public function setHorizontal($pCellCoordinate = 'A1', $pValue = self::HORIZONTAL_CENTER)
    {
        $sheet = $this->getObjWorkSheet();
        $sheet->getStyle($pCellCoordinate)->getAlignment()->setHorizontal($pValue);
    }

    public function setVerticalAndHorizontalCenter($pCellCoordinate = 'A1')
    {
        $this->setVertical($pCellCoordinate);
        $this->setHorizontal($pCellCoordinate);
    }

    public function setBold($pCellCoordinate = 'A1')
    {
        $sheet = $this->getObjWorkSheet();
        $sheet->getStyle($pCellCoordinate)->applyFromArray(array('font' => array('bold' => true)));
    }

    public function setDefaultFontName($name = '宋体')
    {
        $sheet = $this->getObjWorkSheet();
        $options = [
            'font' => [
                'name' => $name
            ]
        ];
        $sheet->getDefaultStyle()->applyFromArray($options);
    }

    public function setWrapText($pCellCoordinate)
    {
        $sheet = $this->getObjWorkSheet();
        $sheet->getStyle($pCellCoordinate)->getAlignment()->setWrapText(true);
    }

    /**
     * 保存为文件
     * @param string $path
     * User: hjun
     * Date: 2018-09-15 17:14:34
     * Update: 2018-09-15 17:14:34
     * Version: 1.00
     */
    public function save($path = DATA_PATH)
    {
        $fileName = $this->getFileName();
        $this->objWriter->save($path . $fileName);
    }

    public function export()
    {
        $fileName = $this->getFileName();
        $fileName = iconv("utf-8", "gb2312", $fileName);
        $objPHPExcel = $this->getObjPHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $objWriter = $this->getObjWriter();
        $objWriter->save('php://output');
    }

    /**
     * 魔术方法
     * @param $method
     * @param $args
     * @return mixed
     * User: hjun
     * Date: 2018-09-15 15:52:20
     * Update: 2018-09-15 15:52:20
     * Version: 1.00
     */
    public function __call($method, $args)
    {
        $objPHPExcel = $this->getObjPHPExcel();
        if (method_exists($objPHPExcel, $method)) {
            return call_user_func_array(array($objPHPExcel, $method), $args);
        } else {
            E(__CLASS__ . ':' . $method . L('_METHOD_NOT_EXIST_'));
            return false;
        }
    }

    public function setCellValue($pCoordinate,$value){
        $this->objWorkSheet->setCellValue($pCoordinate, $value);
    }
}