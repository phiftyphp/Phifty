<?php
namespace Phifty\Command;
use CLIFramework\Command;
use ConfigKit\ConfigCompiler;

class BuildConfCommand extends Command
{
    public function brief()
    {
        return 'build PHP configuration file from YAML.';
    }

    public function usage()
    {
        return 'build-conf [yaml filepath]';
    }

    public function execute()
    {
        $configPath = func_get_args();

        // should we scan config directories ?
        if (empty($configPath)) {
            $configPath = array_filter(
                array(
                    'config/application.yml',
                    'config/framework.yml',
                    'config/database.yml',
                    'config/testing.yml'
                ), function($file) {
                            return file_exists($file);
                        });
        }

        if (empty($configPath)) {
            die("No config found.");
        }

        foreach ($configPath as $path) {
            $this->logger->info( "Building config file $path" );
            ConfigCompiler::compile($path);
        }
        $this->logger->info('Done');
    }
}
