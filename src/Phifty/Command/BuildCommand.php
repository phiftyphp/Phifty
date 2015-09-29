<?php
namespace Phifty\Command;
use CLIFramework\Command;
use ConfigKit\ConfigCompiler;
use ConfigKit\ConfigLoader;
use CodeGen\Generator\AppClassGenerator;
use CodeGen\Block;
use CodeGen\Raw;
use CodeGen\Statement\RequireStatement;
use CodeGen\Statement\RequireComposerAutoloadStatement;
use CodeGen\Statement\RequireClassStatement;
use CodeGen\Statement\AssignStatement;
use CodeGen\Statement\DefineStatement;
use CodeGen\Expr\NewObject;


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


    /*
        Build command generates the bootstrap.php script in the following sections:

        ----------------------------
        Predefined Constants
        ----------------------------
        Global variable definitions
        ----------------------------
        Require & init class loaders
        ----------------------------
        Create config loader object
        ----------------------------
        Create Kernel object
        ----------------------------
        Register ServiceProviders
        ----------------------------
        Register Apps
        ----------------------------
        Register Bundles
        ----------------------------
        Init Kernel object
        ----------------------------

     */
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

        if (extension_loaded('mbstring')) {
            $block[] = "mb_internal_encoding('UTF-8');";
        }


        // autoload script from composer
        $block[] = new DefineStatement('PH_ROOT', PH_ROOT);
        $block[] = new DefineStatement('PH_APP_ROOT', PH_ROOT);
        $block[] = new DefineStatement('DS', DIRECTORY_SEPARATOR);
        // $block[] = sprintf("define('PH_ROOT', %s);", var_export(PH_ROOT, true));
        // $block[] = sprintf("define('PH_APP_ROOT', %s);", var_export(PH_APP_ROOT, true));
        // $block[] = "defined('DS') || define('DS', DIRECTORY_SEPARATOR);";


        // CLI mode should be dynamic
        $block[] = new DefineStatement('CLI', new Raw("isset(\$_SERVER['argc']) && !isset(\$_SERVER['HTTP_HOST'])"));
        $block[] = new DefineStatement('CLI_MODE', new Raw("CLI"));


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
        $configClassGenerator = new AppClassGenerator([ 'namespace' => 'App', 'prefix' => 'App' ]);
        $configClass = $configClassGenerator->generate($configLoader);
        $classPath = $configClass->generatePsr4ClassUnder('app'); 
        require_once $classPath;
        $block[] = new RequireStatement(PH_APP_ROOT . DIRECTORY_SEPARATOR . $classPath);

        $kernelClassGenerator = new AppClassGenerator([
            'namespace' => 'App',
            'prefix' => 'App',
            'property_filter' => function($property) {
                return !preg_match('/^(applications|services|environment|isDev|_.*)$/i', $property->getName());
            }
        ]);
        $runtimeKernel = new \Phifty\Kernel;
        $runtimeKernel->prepare($configLoader);
        $appKernelClass = $kernelClassGenerator->generate($runtimeKernel);
        $classPath = $appKernelClass->generatePsr4ClassUnder('app'); 
        require_once $classPath;
        $block[] = new RequireStatement(PH_APP_ROOT . DIRECTORY_SEPARATOR . $classPath);

        // $block[] = '';



        // Include bootstrap class
        $block[] = new RequireStatement(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Bootstrap.php' );



        // Kernel initialization after bootstrap script
        if ($configLoader->isLoaded('framework')) {
            if ($configLoader->isLoaded('database')) {
                $dbConfig = $configLoader->getSection('database');
                $block[] = '$kernel->registerService(new \Phifty\ServiceProvider\DatabaseServiceProvider(' . var_export($dbConfig, true) . '));';
            }

            // Require application classes directly, we need applications to be registered before services
            if ($appConfigs = $configLoader->get('framework', 'Applications')) {

                $appDir = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'applications';

                foreach ($appConfigs as $appName => $appconfig) {
                    $appClassPath = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'applications' . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR . 'Application.php';
                    if (file_exists($appClassPath)) {
                        $block[] = new RequireStatement($appClassPath);
                    }
                    $block[] = "\$splClassLoader->addNamespace(['$appName' => '$appDir']);";
                }
            }

            if ($services = $configLoader->get('framework', 'ServiceProviders')) {
                foreach ($services as $name => $options) {
                    if (!$options) {
                        $options = array();
                    }

                    // not full qualified classname
                    $class = (false === strpos($name, '\\')) ? ('Phifty\\ServiceProvider\\'.$name) : $name;
                    if (class_exists($class, true)) {
                        $block[] = new RequireClassStatement($class);

                        if (is_subclass_of($class, 'Phifty\\ServiceProvider\\BaseServiceProvider')) {
                            $block[] = '$kernel->registerService(' . $class::generateNew($runtimeKernel, $options) . ');';
                        } else {
                            $expr = new NewObject($class, [$options]);
                            $block[] = '$kernel->registerService(' . $expr->render() . ');';
                        }
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
