<?php
$finder = new LazyRecord\Schema\SchemaFinder;
foreach ( kernel()->applications as $app ) {
    $finder->in( $app->locate() );
}

foreach ( kernel()->bundles as $bundle ) {
    $finder->in( $bundle->locate() );
}

if ( file_exists('tests') ) {
    $finder->in('tests');
}
$finder->find();
return $finder->getSchemas();
