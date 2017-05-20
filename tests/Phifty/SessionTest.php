<?php
use Phifty\Session;

class SessionTest extends \PHPUnit\Framework\TestCase 
{
    /* test method */
    function testSession()
    {
        $session = new Session;
        $this->assertNotNull( $session );

        $session->test = 123;

        $this->assertEquals( $_SESSION['test'] , 123 );

        $session->remove('test');

        $this->assertNotNull( ! isset( $_SESSION['test'] ));

        $session->foo = array(1,2,3);

        $this->assertNotNull( isset($_SESSION['foo']) );
        $session->remove('foo');
    }

    function testSessionPrefix()
    {
        $sen = new Session( '_prefix_' );
        $this->assertNotNull( $sen );
        $sen->account = 'admin';
        $this->assertNotNull( isset($_SESSION['_prefix_account']) );
        $sen->remove('account');
        $this->assertNotNull( ! isset($_SESSION['_prefix_account']) );
    }

}

