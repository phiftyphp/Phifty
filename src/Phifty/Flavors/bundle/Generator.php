<?php
namespace bundle;
use GenPHP\Flavor\BaseGenerator;


/**
 * phifty new bundle {bundle name}
 */
class Generator extends BaseGenerator
{
    public function brief() { return 'generate bundle structure'; }

    public function generate($bundleName)
    {
        $bundleDir = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'bundles' . DIRECTORY_SEPARATOR . $bundleName;

        $this->createDir( $bundleDir . DIRECTORY_SEPARATOR . 'Model' );
        $this->createDir( $bundleDir . DIRECTORY_SEPARATOR . 'Controller' );
        $this->createDir( $bundleDir . DIRECTORY_SEPARATOR . 'Action' );
        $this->createDir( $bundleDir . DIRECTORY_SEPARATOR . 'Templates' );
        $this->createDir( $bundleDir . DIRECTORY_SEPARATOR . 'Assets' );
        $this->createDir( $bundleDir . DIRECTORY_SEPARATOR . 'Configs' );

        $classFile = $bundleDir . DIRECTORY_SEPARATOR . $bundleName . '.php';
        $this->render('Bundle.php.twig', $classFile, [ 'bundleName' => $bundleName ]);

        $classFile = $bundleDir . DIRECTORY_SEPARATOR . 'Translation.php';
        $this->render('Translation.php.twig', $classFile, []);

        // registering bundle to config
        // $config = yaml_parse(file_get_contents('config/framework.yml'));
        // $config['Plugins'][ $bundleName ] = array();
        // file_put_contents('config/framework.yml', yaml_emit($config) );
    }
}
