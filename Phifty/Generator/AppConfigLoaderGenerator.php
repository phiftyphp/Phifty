<?php

namespace Phifty\Generator;

use ConfigKit\ConfigLoader;
use CodeGen\Generator\AppClassGenerator;

class AppConfigLoaderGenerator
{
    public static function generate(ConfigLoader $configLoader, $appNamespace, $appDir)
    {
        $generator = new AppClassGenerator([
            'namespace' => $appNamespace,
            'prefix' => '',
        ]);
        $class = $generator->generate($configLoader);
        return $class->generatePsr4ClassUnder($appDir);
    }
}
