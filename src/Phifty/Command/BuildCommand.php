<?php
namespace Phifty\Command;
use CLIFramework\Command;
use ConfigKit\ConfigCompiler;
use ConfigKit\ConfigLoader;
use CodeGen\Generator\AppClassGenerator;
use CodeGen\Block;
use CodeGen\Statement\RequireStatement;
use CodeGen\Statement\AssignStatement;


class BuildCommand extends Command
{
    public function brief()
    {
        return 'build application. (generates main.php)';
    }

    public function options($opts)
    {
        $opts->add('o|output:=string', 'output file');
    }

    public function execute()
    {
        // XXX: connect to differnt config by using environment variable (PHIFTY_ENV)
        $this->logger->info("Building config files...");
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


        $this->logger->info('Generating main.php...');

        defined('PH_APP_ROOT') || define('PH_APP_ROOT', getcwd());
        // PH_ROOT is deprecated, but kept for backward compatibility
        defined('PH_ROOT') || define('PH_ROOT', getcwd());

        $this->logger->info('PH_APP_ROOT:' . PH_APP_ROOT);


        $outputFile = $this->options->output ?: 'main.php';


        $block = new Block;

        $block[] = '<?php';

        // autoload script from composer
        $block[] = sprintf("define('PH_ROOT', %s);", var_export(PH_ROOT, true));
        $block[] = sprintf("define('PH_APP_ROOT', %s);", var_export(PH_APP_ROOT, true));

        $block[] = 'global $composerClassLoader;';
        $block[] = new AssignStatement('$composerClassLoader', new RequireStatement(PH_APP_ROOT . DIRECTORY_SEPARATOR . 'vendor/autoload.php'));

        $this->logger->info("Generating config loader...");
        // generating the config loader
        $configLoader = self::createConfigLoader(PH_APP_ROOT);
        $generator = new AppClassGenerator([ 'namespace' => 'App', 'prefix' => '' ]);
        $appClass = $generator->generate($configLoader);
        $path = $appClass->generatePsr4ClassUnder('app'); 
        require_once $path;
        $block[] = new RequireStatement(PH_APP_ROOT . DIRECTORY_SEPARATOR . $path);



        // FIXME:
        if (extension_loaded('apc')) {

        }
        $block[] = new RequireStatement(PH_APP_ROOT . DIRECTORY_SEPARATOR . 'vendor/corneltek/universal/src/Universal/ClassLoader/SplClassLoader.php');
        $block[] = 'global $splClassLoader;';
        $block[] = '$splClassLoader = new \Universal\ClassLoader\SplClassLoader();';
        $block[] = '$splClassLoader->useIncludePath(false);';
        $block[] = '$splClassLoader->register(false);';


        // Include bootstrap class
        $block[] = new RequireStatement(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Bootstrap.php' );

        /*
        if (0 && extension_loaded('apc')) {
            // require PH_APP_ROOT . '/vendor/corneltek/universal/src/Universal/ClassLoader/ApcClassLoader.php';
            $loader = new \Universal\ClassLoader\ApcClassLoader(PH_ROOT);
        } else {
            // require PH_APP_ROOT . '/vendor/corneltek/universal/src/Universal/ClassLoader/SplClassLoader.php';
            $loader = new \Universal\ClassLoader\SplClassLoader();
        }
         */




        $this->logger->info("Compiling code to $outputFile");
        $code = $block->render();




        echo $code;
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
