<?php
class ViewTest extends PHPUnit_Framework_TestCase
{

    public function testView()
    {
        $view = new Phifty\View;
        ok($view->Kernel);
        ok($view['Kernel']);
        foreach( $view as $name => $val ) {
            ok($val);
            ok($name);
        }
    }
}


