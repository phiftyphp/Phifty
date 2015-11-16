<?php
namespace controller;
use GenPHP\Flavor\BaseGenerator;
use Exception;

class Generator extends BaseGenerator
{
    public function brief() { return 'generate controller class'; }

    public function generate($ns, $controllerName)
    {
        $app = strtolower($ns) == 'app' ? kernel()->getApp() : (kernel()->bundle($ns) ?: kernel()->bundles->load($ns));
        if (! $app) {
            throw new Exception("Application or bundle not found.");
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
