<?php
use Phifty\Session;

class SessionTest extends \PHPUnit\Framework\TestCase 
{
    /* test method */
    function testSession()
    {
        $session = new Session;
        ok( $session );

        $session->test = 123;

        $this->assertEquals( $_SESSION['test'] , 123 );

        $session->remove('test');

        ok( ! isset( $_SESSION['test'] ));

        $session->foo = array(1,2,3);

        ok( isset($_SESSION['foo']) );
        $session->remove('foo');
    }

    function testSessionPrefix()
    {
        $sen = new Session( '_prefix_' );
        ok( $sen );
        $sen->account = 'admin';
        ok( isset($_SESSION['_prefix_account']) );
        $sen->remove('account');
        ok( ! isset($_SESSION['_prefix_account']) );
    }

}

