<?php
namespace Phifty\Command;
use CLIFramework\Command;
use ConfigKit\ConfigCompiler;
use ConfigKit\ConfigLoader;
use CodeGen\Generator\AppClassGenerator;

class BuildCommand extends Command
{
    public function brief()
    {
        return 'build application. (generates main.php)';
    }

    public function execute()
    {
        $this->logger->info('Generating main.php...');
        if (!defined('PH_APP_ROOT')) {
            define('PH_APP_ROOT', getcwd());
        }
        $this->logger->info('PH_APP_ROOT:' . PH_APP_ROOT);
        $configLoader = self::createConfigLoader(PH_APP_ROOT);



        $generator = new AppClassGenerator([ 'namespace' => 'App', 'prefix' => '' ]);
        $appClass = $generator->generate($configLoader);

        $path = $appClass->generatePsr4ClassUnder('app'); 
        require_once $path;
        // echo file_get_contents($path);
    }

    static public function createConfigLoader($baseDir)
    {
        // We load other services from the definitions in config file
        // Simple load three config files (framework.yml, database.yml, application.yml)
        $loader = new ConfigLoader();
        if (file_exists($baseDir.'/config/framework.yml')) {
            $loader->load('framework', $baseDir.'/config/framework.yml');
        }

        // This is for DatabaseService
        if (file_exists($baseDir.'/db/config/database.yml')) {
            $loader->load('database', $baseDir.'/db/config/database.yml');
        } elseif (file_exists($baseDir.'/config/database.yml')) {
            $loader->load('database', $baseDir.'/config/database.yml');
        }

        // Config for application, services does not depends on this config file.
        if (file_exists($baseDir.'/config/application.yml')) {
            $loader->load('application', $baseDir.'/config/application.yml');
        }

        // Only load testing configuration when environment
        // is 'testing'
        if (getenv('PHIFTY_ENV') === 'testing') {
            if (file_exists($baseDir.'/config/testing.yml')) {
                $loader->load('testing', ConfigCompiler::compile($baseDir.'/config/testing.yml'));
            }
        }
        return $loader;
    }


}
