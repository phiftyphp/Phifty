<?php
namespace crud_handler;

use GenPHP\Flavor\BaseGenerator;
use Exception;
use Doctrine\Common\Inflector\Inflector;

class Generator extends BaseGenerator
{
    public function brief() { return 'generate CRUDHandler class'; }

    public function generate($ns,$modelName,$crudId = null)
    {
        $bundle = kernel()->getApp() ?: kernel()->bundle($ns,true);
        if (! $bundle) {
            throw new Exception("$ns application or bundle not found.");
        }

        if (! $crudId) {
            $crudId = Inflector::tableize($modelName);
        }

        $bundleName = $bundle->getNamespace();
        $modelClass = $bundleName . '\\Model\\' . $modelName;

        $handlerClass = $modelName . 'CRUDHandler';
        $classFile = $bundle->locate() . DIRECTORY_SEPARATOR . $handlerClass . '.php';

        $this->render('CRUDHandler.php.twig',$classFile,array(
            'handlerClass' => $handlerClass,
            'bundleName'   => $bundleName,
            'modelClass'   => $modelClass,
            'crudId'       => $crudId,
        ));
    }
}
