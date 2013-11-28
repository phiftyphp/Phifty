<?php
class ViewTest extends PHPUnit_Framework_TestCase
{

    function testView()
    {
        $view = new Phifty\View;
        ok($view,'got view');
        ok($view->Kernel);
        ok($view['Kernel']);
        foreach( $view as $name => $val ) {
            ok($val);
            ok($name);
        }
    }


    function testViewTwigEngine()
    {
        $engine = \Phifty\View\Engine::createEngine( 'twig' );
        ok($engine);
        isa_ok( 'Phifty\View\Twig' , $engine );

# FIXME:
#          $result = $engine->render( 'test/twig.html' , array( 'Msg' => 'Hello World' ) );
#          ok( $result );
#          ok( strpos( $result , 'Hello World' ) !== false );

        $engine2 = \Phifty\View\Engine::createEngine( 'twig' );
        ok( $engine2 );

        $string = $engine2->renderString( 'Hello {{ name }}' , array( 'name' => 'John' ) );
        ok( $string );
        ok( strpos( $string , 'John' ) !== false );
    }


    function testActionResult()
    {
        $view = new \Phifty\View;
        $view->result = new \ActionKit\Result;
        $view->result->success('Yes');
        like('/success/', $view->render( '@CoreBundle/phifty/action_result.html' ));
        like('/Yes/', $view->render( '@CoreBundle/phifty/action_result.html' ));
    }

    function testActionResult2()
    {
        $view = new \Phifty\View;
        $view->result = new \ActionKit\Result;
        $view->result->error('No');
        like('/error/', $view->render( '@CoreBundle/phifty/action_result.html' ));
        like('/No/', $view->render( '@CoreBundle/phifty/action_result.html' ));
    }

}


