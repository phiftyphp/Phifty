<?php
namespace controller;
use GenPHP\Flavor\BaseGenerator;
use Exception;

class Generator extends BaseGenerator
{
    public function brief() { return 'generate controller class'; }

    public function generate($ns,$controllerName)
    {
        $app = kernel()->app($ns) ?: kernel()->plugin($ns) ?: kernel()->plugins->load($ns);
        if (! $app) {
            throw new Exception("$ns application or plugin not found.");
        }

        if ( strrpos($controllerName,'Controller') === false ) {
            $controllerName .= 'Controller';
        }

        $args = func_get_args();
        $args = array_splice($args,2);
        $controllerActions = array('indexAction');
        foreach ($args as $arg) {
            $controllerActions[] = $arg . 'Action';
        }

        $dir = $app->locate();
        $className = $ns . '\\Controller\\' . $controllerName;
        $classDir = $dir . DIRECTORY_SEPARATOR . 'Controller';
        $classFile = $classDir . DIRECTORY_SEPARATOR . $controllerName . '.php';

        if ( ! file_exists($classDir) ) {
            mkdir($classDir, 0755, true);
        }

        if ( file_exists($classFile) ) {
            $this->logger->info("Found existing $classFile, skip");

            return;
        }

        $this->render('Controller.php.twig',$classFile,array(
            'namespace' => $ns,
            'controllerName' => $controllerName,
            'controllerActions' => $controllerActions,
        ));
    }

}
