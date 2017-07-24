<?php
namespace schema;
use GenPHP\Flavor\BaseGenerator;
use Exception;


/**
 * $ phifty new schema StaffBundle Staff name:varchar:30
 */
class Generator extends BaseGenerator
{
    public function brief() { return 'generate schema class'; }

    public function generate($ns,$schemaName)
    {
        $app = strtolower($ns) == 'app' ? kernel()->getApp() : (kernel()->bundle($ns) ?: kernel()->bundles->load($ns));
        if ( ! $app) {
            throw new Exception("$ns application or plugin not found.");
        }

        if ( strrpos($schemaName,'Schema') === false ) {
            $schemaName .= 'Schema';
        }

        $args = func_get_args();
        $args = array_splice($args,2);
        $schemaColumns = array();
        foreach ($args as $arg) {
            $list = explode(':',$arg);
            $schemaColumns[] = array('name' => $list[0], 'type' => $list[1], 'var' => @$list[2]);
        }

        $dir = $app->locate();
        $className = $ns . '\\Model\\' . $schemaName;
        $classDir = $dir . DIRECTORY_SEPARATOR . 'Model';
        $classFile = $classDir . DIRECTORY_SEPARATOR . $schemaName . '.php';

        if ( ! file_exists($classDir) ) {
            mkdir($classDir, 0755, true);
        }

        if ( file_exists($classFile) ) {
            $this->logger->info("Found existing $classFile, skip");
            return;
        }

        $this->render('Schema.php.twig',$classFile,array(
            'namespace' => $ns,
            'schemaName' => $schemaName,
            'schemaColumns' => $schemaColumns,
        ));
    }

}
