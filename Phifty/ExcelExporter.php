<?php
namespace Phifty;
use PHPExcel;
use PHPExcel_IOFactory;

/*
 *
    $exporter = new ExcelExporter
    $exporter->getExcel()->getProperties()
        ->setTitle()
        ->setLastModifiedBy();
    $exporter->putCells( .... );
    $exporter->writeToStream( 'blah.xls' );

*/
class ExcelExporter
{
    public $columns;
    public $excel;
    public $sheet;
    public $currentRow;
    public $currentCol;

    public function __construct( )
    {
        $this->excel = new \PHPExcel();
        $this->currentRow = 1;
        $this->currentCol = 1;
    }

    public function getExcel()
    {
        return $this->excel;
    }

    /* convert column to column name 1 => A1 */
    public function convertColName( $i )
    {
        $columnName = '';
        while ($i > 0) {
            $modulo = ($i - 1) % 26;
            $columnName = chr( 65 + $modulo ) . $columnName;
            $i = (int) (($i - $modulo) / 26);
        }

        return $columnName;
    }

    public function nextRow()
    {
        $this->currentRow++;
        $this->currentCol = 1;
    }

    public function nextCol()
    {
        return ++$this->currentCol;
    }

    public function setSheet($index = 0)
    {
        return $this->sheet = $this->excel->setActiveSheetIndex($index);
    }

    public function getSheet()
    {
        if ( $this->sheet )

            return $this->sheet;
    }

    public function putCells( $cells , $at = 1 )
    {
        $this->currentCol = $at;
        $sheet = $this->getSheet();
        if ( ! $sheet )
            $sheet = $this->setSheet(0);

        foreach ($cells as $cell) {
            $pos = $this->convertColName( $this->currentCol++ ) . $this->currentRow;
            $sheet->setCellValueExplicit( $pos , $cell );  // string type
        }
        $this->nextRow();
    }

    /*
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
     */

    public function writeToStream( $filename = 'ExportData.xls' )
    {
        $excel = $this->excel;

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $excel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel5)
        header('Pragma: public'); // required
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false); // required for certain browsers
        header('Cache-Control: max-age=0');

        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$filename\"");
        header('Content-Transfer-Encoding: binary');

        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save('php://output');
    }

    /*
    public function writeToFile($filename)
    {
        $objPHPExcel = $this->excel;

        // $this->setMetaData( $this->excel );

        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $this->writeCollection( $sheet , $this->collection );

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel5)
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save( $filename );
    }
    */

}
