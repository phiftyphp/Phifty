<?php
use Phifty\ExcelExporter;

class ExcelTest extends PHPUnit_Framework_TestCase
{
    function testExcel()
    {
        if (!class_exists('PHPExcel')) {
            $this->markTestSkipped('PHPExcel is required for testing');
        }
        $excel = new ExcelExporter;
        $excel->setSheet(0);
        $excel->putCells( array('Test','Foo','Bar') );
        ok( $excel );
    }
}

