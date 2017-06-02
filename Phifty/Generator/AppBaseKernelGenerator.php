<?php

namespace Phifty\Generator;

use Phifty\Kernel;
use ConfigKit\ConfigLoader;
use CodeGen\Generator\AppClassGenerator;

class AppBaseKernelGenerator
{
    public static function generate(Kernel $kernel, $appNamespace , $appDir)
    {
        $generator = new AppClassGenerator([
            "namespace" => $appNamespace,
            "prefix" => "Base",
            "property_filter" => function ($property) {
                return !preg_match('/^(applications|services|environment|is\w+|_.*)$/i', $property->getName());
            }
        ]);
        $class = $generator->generate($kernel);
        return $class->generatePsr4ClassUnder($appDir);
    }
}
