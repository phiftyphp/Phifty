<?php

namespace Phifty\Schema\Loader;

use Maghead\Schema\Loader\FileSchemaLoader;

class AppSchemaLoader extends FileSchemaLoader
{
    function __construct(array $paths = []) {
        parent::__construct($paths);

        $kernel = kernel();
        if ($app = $kernel->getApp()) {
            $this->addPath($app->locate() . DIRECTORY_SEPARATOR . 'Model');
        }
        if ($bundles = $kernel->bundles) {
            foreach ($bundles as $bundle) {
                $this->addPath($bundle->locate() . DIRECTORY_SEPARATOR . 'Model');
            }
        }
    }
}
