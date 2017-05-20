<?php
use Phifty\Generator\TestSuiteGenerator;
use CRUD\CRUD;
use App\AppKernel;

class TestSuiteGeneratorTest extends \PHPUnit\Framework\TestCase
{
    public function testGenerator()
    {
        if (!class_exists('CRUD\\CRUD', true)) {
            return $this->markTestSkipped('Require CRUD bundle to test');
        }

        $generator = new TestSuiteGenerator;
        $generator->addFromBundle(new CRUD(new AppKernel, [ ]) );
        echo $generator->toXml();
    }
}




