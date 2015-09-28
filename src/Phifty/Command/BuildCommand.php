<?php
namespace Phifty\Command;
use CLIFramework\Command;
use ConfigKit\ConfigCompiler;
use ConfigKit\ConfigLoader;
use CodeGen\Generator\AppClassGenerator;
use CodeGen\Block;
use CodeGen\Statement\RequireStatement;
use CodeGen\Statement\RequireComposerAutoloadStatement;
use CodeGen\Statement\RequireClassStatement;
use CodeGen\Statement\AssignStatement;


function PhiftyClassPath($class)
{
    return dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
}

class BuildCommand extends Command
{
    public function brief()
    {
        return 'build bootstrap script';
    }

    public function options($opts)
    {
        $opts->add('o|output:=string', 'output file')
            ->defaultValue('bootstrap.php');
    }

    public function execute()
    {
        // XXX: connect to differnt config by using environment variable (PHIFTY_ENV)
        $this->logger->info("===> Building config files...");
        $configPaths = array_filter(
            array(
                'config/application.yml',
                'config/framework.yml',
                'config/database.yml',
                'config/testing.yml'
            ), 'file_exists');
        foreach ($configPaths as $configPath) {
            $this->logger->info("Building $configPath");
            ConfigCompiler::compile($configPath);
        }

        $outputFile = $this->options->output;

        $this->logger->info("===> Generating bootstrap file: $outputFile");

        defined('PH_APP_ROOT') || define('PH_APP_ROOT', getcwd());
        // PH_ROOT is deprecated, but kept for backward compatibility
        defined('PH_ROOT') || define('PH_ROOT', getcwd());

        $this->logger->info('PH_APP_ROOT:' . PH_APP_ROOT);


        $block = new Block;
        $block[] = '<?php';

        // autoload script from composer
        $block[] = sprintf("define('PH_ROOT', %s);", var_export(PH_ROOT, true));
        $block[] = sprintf("define('PH_APP_ROOT', %s);", var_export(PH_APP_ROOT, true));
        $block[] = "defined('DS') || define('DS', DIRECTORY_SEPARATOR);";


        $block[] = 'global $kernel;';
        $block[] = 'global $composerClassLoader;';
        $block[] = 'global $splClassLoader;';
        $block[] = new AssignStatement('$composerClassLoader', new RequireComposerAutoloadStatement());

        // TODO:
        //  - add PSR-4 class loader here.
        $block[] = new RequireClassStatement('Universal\\ClassLoader\\SplClassLoader');
        $block[] = '$splClassLoader = new \Universal\ClassLoader\SplClassLoader();';
        $block[] = '$splClassLoader->useIncludePath(false);';
        $block[] = '$splClassLoader->register(false);';


        $block[] = new RequireClassStatement('Universal\\Container\\ObjectContainer');
        $block[] = new RequireClassStatement('Phifty\\Kernel');

        $this->logger->info("Generating config loader...");
        // generating the config loader
        $configLoader = self::createConfigLoader(PH_APP_ROOT);
        $generator = new AppClassGenerator([ 'namespace' => 'App', 'prefix' => '' ]);
        $appClass = $generator->generate($configLoader);
        $path = $appClass->generatePsr4ClassUnder('app'); 
        require_once $path;
        $block[] = new RequireStatement(PH_APP_ROOT . DIRECTORY_SEPARATOR . $path);




        // Include bootstrap class
        $block[] = new RequireStatement(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Bootstrap.php' );


        // Kernel initialization after bootstrap script
        if ($configLoader->isLoaded('framework')) {
            if ($configLoader->isLoaded('database')) {
                $dbConfig = $configLoader->getSection('database');
                $block[] = '$kernel->registerService(new \Phifty\ServiceProvider\DatabaseServiceProvider(' . var_export($dbConfig, true) . '));';
            }
            if ($services = $configLoader->get('framework', 'ServiceProviders')) {
                foreach ($services as $name => $options) {
                    // not full qualified classname
                    $class = (false === strpos($name, '\\')) ? ('Phifty\\ServiceProvider\\'.$name) : $name;
                    if (class_exists($class, true)) {
                        $block[] = new RequireClassStatement($class);
                        $block[] = '$kernel->registerService(new ' . $class . '(), ' . var_export($options, true) . ');';
                    }
                }
            }

            // Require application classes directly
            if ($appconfigs = $configLoader->get('framework', 'Applications')) {
                foreach ($appconfigs as $appName => $appconfig) {
                    $appClassPath = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'applications' . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR . 'Application.php';
                    if (file_exists($appClassPath)) {
                        $block[] = new RequireStatement($appClassPath);
                    }
                }
            }
        }

        $this->logger->info("===> Compiling code to $outputFile");
        $code = $block->render();
        $this->logger->debug($code);
        return file_put_contents($outputFile, $code);
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
