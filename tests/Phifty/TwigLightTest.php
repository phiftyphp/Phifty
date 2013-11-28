<?php
use Phifty\View\TwigLight;

class TwigLightTest extends PHPUnit_Framework_TestCase
{
    function testTwigLight()
    {
        // write your test code here
        $twig = TwigLight::getEngine();
        ok( $twig );
    }
}


