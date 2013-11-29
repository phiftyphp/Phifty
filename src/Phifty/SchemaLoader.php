<?php
$finder = new LazyRecord\Schema\SchemaFinder;
foreach ( kernel()->applications as $app ) {
    $finder->in( $app->locate() );
}

foreach ( kernel()->bundles as $bundle ) {
    $finder->in( $bundle->locate() );
}

$finder->in( PH_ROOT . DIRECTORY_SEPARATOR . 'tests' );
$finder->find();
return $finder->getSchemas();
