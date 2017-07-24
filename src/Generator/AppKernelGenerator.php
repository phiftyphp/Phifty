<?php

namespace Phifty\Generator;

use Phifty\Kernel;
use ConfigKit\ConfigLoader;
use CodeGen\Generator\AppClassGenerator;
use CodeGen\UserClass;

class AppKernelGenerator
{
    public static function generate(Kernel $kernel, $appNamespace, $appDir)
    {
        $class = new UserClass("\\{$appNamespace}\\Kernel");
        $class->extendClass("\\{$appNamespace}\\BaseKernel");

        $classPath = $class->getPsr4ClassPathUnder($appDir);
        if (!file_exists($classPath)) {
            $class->generatePsr4ClassUnder($appDir);
        }

        return $classPath;
    }
}
