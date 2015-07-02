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

    public function testViewTwigEngine()
    {
        $engine = \Phifty\View\Engine::createEngine( 'twig' );
        ok($engine);
        isa_ok( 'Phifty\View\Twig' , $engine );

# FIXME:
#          $result = $engine->render( 'test/twig.html' , array( 'Msg' => 'Hello World' ) );
#          ok( $result );
#          ok( strpos( $result , 'Hello World' ) !== false );

        $engine2 = \Phifty\View\Engine::createEngine( 'twig' );
        $this->assertNotNull( $engine2 );

        $string = $engine2->renderString( 'Hello {{ name }}' , array( 'name' => 'John' ) );
        $this->assertNotNull($string);
        ok( strpos( $string , 'John' ) !== false );
    }
}


