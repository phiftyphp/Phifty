<?php

namespace Phifty\Schema\Finder;

use Maghead\Schema\Finder\FileSchemaFinder;
use Phifty\Kernel;

class AppSchemaFinder extends FileSchemaFinder
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
