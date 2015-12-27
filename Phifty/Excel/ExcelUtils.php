<?php
namespace Phifty\Excel;

class ExcelUtils
{

    /**
     * @param integer column index (1,2,3,4....)
     *
     * @return string A, B, C
     */
    public static function convert_column_name( $i )
    {
        $columnName = '';
        while ($i > 0) {
            $modulo = ($i - 1) % 26;
            $columnName = chr( 65 + $modulo ) . $columnName;
            $i = (int) (($i - $modulo) / 26);
        }

        return $columnName;
    }

}
