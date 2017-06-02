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
            if (file_exists($app->getModelDir())) {
                $this->addPath($app->getModelDir());
            }
        }
        if ($bundles = $kernel->bundles) {
            foreach ($bundles as $bundle) {
                if (file_exists($bundle->getModelDir())) {
                    $this->addPath($bundle->getModelDir());
                }
            }
        }
    }
}
