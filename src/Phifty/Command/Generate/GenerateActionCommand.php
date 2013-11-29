<?php
namespace Phifty\Command\Generate;
use CLIFramework\Command;
use ActionKit\ActionGenerator;

class GenerateActionCommand extends Command
{

    public function brief() { return 'generate action class'; }

    public function usage() { return '[application name|bundle name] [action name]'; }

    public function execute($ns,$actionName)
    {
        $app = kernel()->app($ns) ?: kernel()->bundle($ns);
        if (! $app) {
            throw new Exception("$ns application or bundle not found.");
        }

        $dir = $app->locate();
        $className = $ns . '\\Action\\' . $actionName;
        $actionDir = $dir . DIRECTORY_SEPARATOR . 'Action';
        $classFile = $actionDir . DIRECTORY_SEPARATOR . $actionName . '.php';

        if ( ! file_exists($actionDir) ) {
            mkdir($actionDir, 0755, true);
        }

        $relfilepath = substr($classFile,strlen(getcwd()) + 1);
        if ( ! file_exists($classFile) ) {
            $gen = new ActionGenerator(array( 'cache' => true ));
            $ret = $gen->generateActionClassCode( $ns , $actionName );
            file_put_contents( $classFile , "<?php\n" . $ret->code . "\n\n?>" );
            $this->logger->info( 'create ' . $ret->action_class . ' => ' . $relfilepath , 1 );
            $this->logger->info( 'done' );
        } else {
            $this->logger->warn( $relfilepath . ' class file exists.' );
        }
    }
}
