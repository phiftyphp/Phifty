<?php
namespace Phifty\Excel;
use PHPExcel_IOFactory;

// need to redesign this

class ExcelExporter
{
    public $collection;
    public $columns;
    public $excel;

    public function __construct( )
    {
        $this->excel = new \PHPExcel();
    }

    public function setCollection( $collection )
    {
        $this->collection = $collection;

        return $this;
    }

    public function setColumns( $columns )
    {
        $this->columns = $columns;

        return $this;
    }

    public function columnName( $i )
    {
        $columnName = '';
        while ($i > 0) {
            $modulo = ($i - 1) % 26;
            $columnName = chr( 65 + $modulo ) . $columnName;
            $i = (int) (($i - $modulo) / 26);
        }

        return $columnName;
    }

    public function writeCollection($sheet,$collection)
    {
        $row = 1;
        $col = 1;

        $model = $this->collection->getModel();
        $labels = array();
        $columns = $this->columns ? $this->columns : $model->getColumnNames();
        foreach ($columns as $name) {
            $label = $labels[$name] = $model->getColumn( $name )->getLabel();
            $cellpos = $this->columnName( $col++ ) . $row;
            $sheet->setCellValue( $cellpos , $label );
        }
        $row++;

        foreach ($this->collection as $id => $item) {
            $col = 1;
            foreach ($columns as $name) {
                $val = $item->value($name);
                $cellpos = $this->columnName( $col++ ) . $row;
                $sheet->setCellValueExplicit( $cellpos , $val );  // string type
            }
            $row++;
        }
        $sheet->setTitle( $model->getLabel() );
    }

    public function setMetaData( $excel )
    {
        // Set properties
        $excel->getProperties()
            ->setCreator("")
            ->setLastModifiedBy("")
            ->setTitle("Office 2007 XLSX Document")
            ->setSubject("Office 2007 XLSX Document")
            ->setDescription("")
            ->setKeywords("")
            ->setCategory("");
    }

    public function writeStream( $filename = 'export.xls' )
    {
        $objPHPExcel = $this->excel;

        $this->setMetaData( $this->excel );

        // Add some data
        $sheet = $objPHPExcel->setActiveSheetIndex(0);

        $this->writeCollection( $sheet , $this->collection );

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel5)
        header("Pragma: public"); // required
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false); // required for certain browsers
        header('Cache-Control: max-age=0');

        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$filename\"");
        header("Content-Transfer-Encoding: binary");

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function writeFile($filename)
    {
        $objPHPExcel = $this->excel;

        $this->setMetaData( $this->excel );

        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $this->writeCollection( $sheet , $this->collection );

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel5)
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save( $filename );
    }

}
