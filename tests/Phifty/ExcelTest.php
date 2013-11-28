<?php
use Phifty\ExcelExporter;

class ExcelTest extends PHPUnit_Framework_TestCase
{
    function testExcel()
    {
        $excel = new ExcelExporter;
        $excel->setSheet(0);
        $excel->putCells( array('Test','Foo','Bar') );
        ok( $excel );
    }
}

