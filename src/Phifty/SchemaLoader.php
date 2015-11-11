<?php
use LazyRecord\Schema\SchemaLoader;
$finder = new LazyRecord\Schema\SchemaFinder;
if ($app = kernel()->getApp()) {
    $finder->in($app->locate());
}


if ($bundles = kernel()->bundles) {
    foreach ($bundles as $bundle) {
        $finder->in( $bundle->locate() );
    }
}

if (file_exists('tests')) {
    $finder->in('tests');
}
$finder->find();
if (method_exists($finder,"getSchemas")) {
    return $finder->getSchemas();
}
return SchemaLoader::loadDeclaredSchemas();
