<?php
$finder = new LazyRecord\Schema\SchemaFinder;
if ($apps = kernel()->applications) {
    foreach ($apps as $app) {
        $finder->in( $app->locate() );
    }
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
return $finder->getSchemas();
