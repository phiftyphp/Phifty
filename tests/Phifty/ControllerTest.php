<?php
class TestController extends Phifty\Controller
{
    public $counter = 0;

    function before() {
        $this->counter++;
    }

    function run()
    {
        $this->counter++;
        return 'yes';
    }

    function after()
    {
        $this->counter++;
    }
}

class ControllerTest extends PHPUnit_Framework_TestCase 
{
    function testController()
    {
        $test = new TestController;
        ok( $test );
        $response = $test->runWrapper(array($test,'run'));
        is( 'yes', $response );

        ok( $test->view() ,'has view' );
        ok( $test->renderJson( array('ok' => 1) ) );
        ok( $test->renderYaml( array('ok' => 1) ) );

        is( 3, $test->counter );
    }
}


