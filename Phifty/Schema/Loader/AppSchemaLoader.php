<?php

namespace Phifty\Schema\Loader;

use Maghead\Schema\Loader\FileSchemaLoader;
use Phifty\Kernel;

class AppSchemaLoader extends FileSchemaLoader
{
    public function __construct(array $paths = [], Kernel $kernel = null)
    {
        parent::__construct($paths);
        if (!$kernel) {
            $kernel = kernel();
        }
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
