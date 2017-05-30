<?php

namespace test_classutils;

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

namespace Phifty;


class ClassUtilsTest extends \PHPUnit\Framework\TestCase
{
    public function testNewClass()
    {
        $obj = ClassUtils::newClass(\test_classutils\foo::class,array( 1,2,3,4,5 ));
        $this->assertInstanceOf('test_classutils\foo', $obj);
        $this->assertEquals(1 , $obj->v1);
        $this->assertEquals(2 , $obj->v2);

        $obj = ClassUtils::newClass(\test_classutils\bar::class);
        $this->assertInstanceOf('test_classutils\bar', $obj );
    }
}
