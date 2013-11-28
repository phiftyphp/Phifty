<?php
namespace test_classutils;
use Phifty\ClassUtils;

class foo {
    public $v1;
    public $v2;
    function __construct( $v1, $v2 ) { 
        $this->v1 = $v1;
        $this->v2 = $v2;
    }
}

class bar {
    public $var;

}

class ClassUtilsTest extends \PHPUnit_Framework_TestCase 
{
    function testClassUtils()
    {
        $obj = ClassUtils::new_class('test_classutils\foo',array( 1,2,3,4,5 ));
        ok( $obj );
        isa_ok( 'test_classutils\foo', $obj );
        is( 1 , $obj->v1 );
        is( 2 , $obj->v2 );

        $obj = ClassUtils::new_class('test_classutils\bar');
        isa_ok( 'test_classutils\bar', $obj );
    }
}


