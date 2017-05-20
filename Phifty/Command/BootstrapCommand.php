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
use CodeGen\Statement\ConstStatement;
use CodeGen\Statement\DefineStatement;
use CodeGen\Statement\AssignStatement;
use CodeGen\Statement\Statement;
use CodeGen\Expr\NewObject;
use CodeGen\Expr\MethodCall;
use CodeGen\Expr\StaticMethodCall;
use CodeGen\Variable;
use CodeGen\Comment;
use CodeGen\CommentBlock;
use ReflectionClass;
use Exception;
use LogicException;
use Universal\ClassLoader\Psr4ClassLoader;

use Maghead\Runtime\Config\FileConfigLoader;

use Phifty\Bundle\BundleLoader;
use Phifty\ServiceProvider\BundleServiceProvider;

function find_db_config($baseDir)
{
    $paths = [
        "{$baseDir}/config/database.yml",
        "{$baseDir}/db/config/database.yml",
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            return FileConfigLoader::compile($path, true);
        }
    }
    return false;
}

class BootstrapCommand extends Command
{
    public function brief()
    {
        return 'build bootstrap script';
    }

    public function aliases()
    {
        return ['b', 'bs'];
    }

    public function options($opts)
    {
        $opts->add('c|clean', 'clean up generated files.');

        $opts->add('e|env:=string', 'environment');

        $opts->add('x|xhprof', 'enable xhprof profiler in the bootstrap file.');

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
        $psr4Map = require "vendor/composer/autoload_psr4.php";

        $psr4ClassLoader = new Psr4ClassLoader;

        // XXX: connect to differnt config by using environment variable (PHIFTY_ENV)
        $this->logger->info("===> Building config files...");
        $configPaths = array_filter([
                'config/application.yml',
                'config/framework.yml',
                'config/testing.yml'
            ], 'file_exists');
        foreach ($configPaths as $configPath) {
            $this->logger->info("Precompiling $configPath ...");
            ConfigCompiler::compile($configPath, true);
        }


        $appDirectory = 'app';

        $outputFile = $this->options->output;

        defined('PH_APP_ROOT') || define('PH_APP_ROOT', getcwd());
        // PH_ROOT is deprecated, but kept for backward compatibility
        defined('PH_ROOT') || define('PH_ROOT', getcwd());
        $this->logger->info('Using PH_APP_ROOT:' . PH_APP_ROOT);

        if ($this->options->clean) {
            $this->logger->info("Removing genereated files");
            $cleanFiles = [
                $outputFile,
                PH_APP_ROOT . $appDirectory . 'AppKernel.php',
                PH_APP_ROOT . $appDirectory . 'AppConfigLoader.php',
            ];

            foreach ($cleanFiles as $cleanFile) {
                $this->logger->debug("Checking $cleanFile");
                if (file_exists($cleanFile)) {
                    $this->logger->debug("Removing $cleanFile");
                    unlink($cleanFile);
                }
            }
            $this->logger->info('Cached files are cleaned up');
            return;
        }


        $this->logger->info("===> Generating bootstrap file: $outputFile");



        $block = new Block;
        $block[] = '<?php';
        $block[] = new CommentBlock([
            "This file is auto-generated through 'bin/phifty bootstrap' command.",
            "Don't modify this file directly",
            "",
            "For more information, please visit https://github.com/c9s/Phifty",
        ]);

        if (extension_loaded('mbstring')) {
            $block[] = "mb_internal_encoding('UTF-8');";
        }

        $xhprof = extension_loaded('xhprof') && $this->options->xhprof;
        if ($xhprof) {
            $block[] = 'xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);';
        }

        // autoload script from composer
        $block[] = new ConstStatement('PH_ROOT', PH_ROOT);
        $block[] = new ConstStatement('PH_APP_ROOT', PH_ROOT);
        $block[] = new ConstStatement('DS', DIRECTORY_SEPARATOR);

        $env = $this->options->env ?: getenv('PHIFTY_ENV') ?: 'development';
        $block[] = new ConstStatement('PH_ENV', $env);

        // $block[] = sprintf("define('PH_ROOT', %s);", var_export(PH_ROOT, true));
        // $block[] = sprintf("define('PH_APP_ROOT', %s);", var_export(PH_APP_ROOT, true));
        // $block[] = "defined('DS') || define('DS', DIRECTORY_SEPARATOR);";


        // CLI mode should be dynamic
        $block[] = new DefineStatement('CLI', new Raw("isset(\$_SERVER['argc']) && !isset(\$_SERVER['HTTP_HOST'])"));
        $block[] = new DefineStatement('CLI_MODE', new Raw("CLI"));

        $block[] = 'global $kernel, $composerClassLoader, $psr4ClassLoader, $splClassLoader;';
        $block[] = new AssignStatement('$composerClassLoader', new RequireComposerAutoloadStatement());


        $block[] = new RequireClassStatement('Universal\\ClassLoader\\Psr4ClassLoader');
        $block[] = '$psr4ClassLoader = new \Universal\ClassLoader\Psr4ClassLoader();';
        $block[] = '$psr4ClassLoader->register(false);';

        $block[] = new Statement(new MethodCall('$psr4ClassLoader', 'addPrefix', [
            'App\\',
            PH_APP_ROOT . DIRECTORY_SEPARATOR . $appDirectory . DIRECTORY_SEPARATOR ]));

        $block[] = new RequireClassStatement('Universal\\ClassLoader\\SplClassLoader');
        $block[] = '$splClassLoader = new \Universal\ClassLoader\SplClassLoader();';
        $block[] = '$splClassLoader->useIncludePath(false);';
        $block[] = '$splClassLoader->register(false);';




        $block[] = new RequireClassStatement('Universal\\Container\\ObjectContainer');
        $block[] = new RequireClassStatement('Phifty\\Kernel');

        $this->logger->info("Generating config loader...");
        // generating the config loader
        $configLoader = $this->createConfigLoader(PH_APP_ROOT);
        $configClassGenerator = new AppClassGenerator([ 'namespace' => 'App', 'prefix' => 'App' ]);
        $configClass = $configClassGenerator->generate($configLoader);
        $classPath = $configClass->generatePsr4ClassUnder($appDirectory);
        $block[] = new RequireStatement(PH_APP_ROOT . DIRECTORY_SEPARATOR . $classPath);
        require_once $classPath;




        $kernelClassGenerator = new AppClassGenerator([
            'namespace' => 'App',
            'prefix' => 'App',
            'property_filter' => function($property) {
                return !preg_match('/^(applications|services|environment|isDev|_.*)$/i', $property->getName());
            }
        ]);

        // The runtime kernel will only contains "configLoader" and "classLoader" services
        $runtimeKernel = new \Phifty\Kernel;
        $runtimeKernel->prepare($configLoader);
        $runtimeKernel->config = function() use ($configLoader) {
            return $configLoader;
        };

        // TODO: load services here?


        // Load the bundle list config
        // The config structure:
        //     BundleLoader:
        //       Paths:
        //       - app_bundles
        //       - bundles
        $bundleLoaderConfig = $configLoader->get('framework','BundleLoader') ?: [ 'Paths' => ['app_bundles','bundles'] ];
        // Load bundle objects into the runtimeKernel
        $bundleLoader = new BundleLoader($runtimeKernel, [
            PH_ROOT . DIRECTORY_SEPARATOR . 'app_bundles',
            PH_ROOT . DIRECTORY_SEPARATOR . 'bundles'
        ]);
        $bundleList = $configLoader->get('framework','Bundles');

        $bundleService = new BundleServiceProvider();
        $bundleService->register($runtimeKernel, $bundleLoaderConfig);


        // Generating registering code for bundle classes
        if ($bundleList) {
            foreach ($bundleList as $bundleName => $bundleConfig) {
                $autoload = $bundleLoader->getAutoloadConfig($bundleName);
                if ($autoload == false) {
                    continue;
                }
                foreach ($autoload as $prefix => $autoloadPath) {
                    if ($psr4Map && isset($psr4Map[$prefix])) {
                        continue;
                    }

                    $realAutoloadPath = realpath($autoloadPath) . DIRECTORY_SEPARATOR;
                    $this->logger->info("Adding psr4 $prefix to $realAutoloadPath");
                    $psr4ClassLoader->addPrefix($prefix, $realAutoloadPath);
                    $block[] = new Statement(new MethodCall('$psr4ClassLoader', 'addPrefix', [ $prefix, $realAutoloadPath ]));
                }
            }
            foreach ($bundleList as $bundleName => $bundleConfig) {
                $bundleClass = "$bundleName\\$bundleName";
                if (!class_exists($bundleClass, true)) {
                    $bundleClassFile = $bundleLoader->findBundleClass($bundleName);
                    if (!$bundleClassFile) {
                        throw new Exception("Bundle $bundleName class file '$bundleClassFile' doesn't exist.");
                    }
                    require $bundleClassFile;
                }
                $runtimeKernel->bundles[$bundleName] = $bundleClass::getInstance($runtimeKernel, $bundleConfig);
            }
        }



        $appKernelClass = $kernelClassGenerator->generate($runtimeKernel);
        $classPath = $appKernelClass->generatePsr4ClassUnder($appDirectory);
        require_once $classPath;
        $block[] = new RequireStatement(PH_APP_ROOT . DIRECTORY_SEPARATOR . $classPath);

        // $block[] = '';

        // Generates: $kernel = new \App\AppKernel;
        $block[] = new AssignStatement('$kernel', new NewObject('App\\AppKernel'));

        // Generates: $kernel->registerService(new \Phifty\ServiceProvider\ClassLoaderServiceProvider($splClassLoader));
        $block[] = new Statement(new MethodCall('$kernel', 'registerService', [ 
            new NewObject('\\Phifty\\ServiceProvider\\ClassLoaderServiceProvider',[ new Variable('$splClassLoader') ]),
        ]));


        // Generates: $configLoader = new \App\AppConfigLoader;
        $block[] = new AssignStatement('$configLoader', new NewObject('App\\AppConfigLoader'));

        // Generates: $kernel->registerService(new \Phifty\ServiceProvider\ConfigServiceProvider($configLoader));
        $block[] = new RequireClassStatement('Phifty\\ServiceProvider\\ConfigServiceProvider');
        $block[] = new Statement(new MethodCall('$kernel', 'registerService', [ 
            new NewObject('\\Phifty\\ServiceProvider\\ConfigServiceProvider', [ new Variable('$configLoader') ]),
        ]));

        // load event service, so that we can bind events in Phifty
        // Generates: $kernel->registerService(new \Phifty\ServiceProvider\EventServiceProvider());
        $block[] = new Comment('The event service is required for every component.');
        $block[] = new RequireClassStatement('Phifty\\ServiceProvider\\EventServiceProvider');
        $block[] = new Statement(new MethodCall('$kernel', 'registerService', [ 
            new NewObject('\\Phifty\\ServiceProvider\\EventServiceProvider'),
        ]));

        // Include bootstrap class
        $block[] = new Comment('Bootstrap.php nows only contains kernel() function.');
        $block[] = new RequireStatement(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Bootstrap.php' );



        // Kernel initialization after bootstrap script
        if ($configLoader->isLoaded('framework')) {


            // This is for DatabaseService
            $dbConfig = find_db_config($baseDir);
            $block[] = '$kernel->registerService(new \Phifty\ServiceProvider\DatabaseServiceProvider(' . var_export([
                'configPath' => $dbConfig,
            ], true) . '));';

            /*
            $block[] = new Statement(new MethodCall('$kernel', 'registerService', [
                $class::generateNew($runtimeKernel, $options),
                $options,
            ]));
             */

            if ($configLoader->isLoaded('database')) {
                $dbConfig = $configLoader->getSection('database');
            }

            // Require application classes directly, we need applications to be registered before services
            if ($appConfigs = $configLoader->get('framework', 'Applications')) {
                $appDir = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'applications';
                foreach ($appConfigs as $appName => $appconfig) {
                    $appClassDir = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'applications' . DIRECTORY_SEPARATOR . $appName;
                    $appClassPath = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'applications' . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR . 'Application.php';
                    if (file_exists($appClassPath)) {
                        $block[] = new RequireStatement($appClassPath);
                    }
                    if (file_exists($appClassDir)) {
                        /*
                        $block[] = new Statement(new MethodCall('$splClassLoader', 'addNamespace', [
                            [ $appName => $appDir ],
                        ]));
                         */
                    }
                }
            } else {
                // TODO: load "App\App" by default
                $appDir = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'app';
                $appClassPath = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'App.php';
                if (file_exists($appClassPath)) {
                    $block[] = new RequireStatement($appClassPath);
                }
                if (is_dir($appDir)) {
                    $block[] = new Statement(new MethodCall('$psr4ClassLoader', 'addPrefix', [
                         'App\\', [$appDir . DIRECTORY_SEPARATOR],
                    ]));
                }
            }

            if ($services = $configLoader->get('framework', 'ServiceProviders')) {
                foreach ($services as $name => $options) {
                    if (!$options) {
                        $options = array();
                    }

                    // Not full qualified classname
                    $class = (false === strpos($name, '\\')) ? ('Phifty\\ServiceProvider\\'.$name) : $name;
                    if (!class_exists($class, true)) {
                        throw new LogicException("$class does not exist.");
                    }
                    $block[] = new RequireClassStatement($class);

                    $this->logger->info("Generating $class ...");

                    $options = $class::canonicalizeConfig($runtimeKernel, $options);
                    if ($options === null) {
                        throw new LogicException("$class::canonicalizeConfig should return an array for service config.");
                    }

                    if (is_subclass_of($class, 'Phifty\\ServiceProvider\\BaseServiceProvider')
                        && $class::isGeneratable($runtimeKernel, $options))
                    {
                        if ($prepareStm = $class::generatePrepare($runtimeKernel, $options)) {
                            $block[] = $prepareStm;
                        }
                        $block[] = new Statement(new MethodCall('$kernel', 'registerService', [
                            $class::generateNew($runtimeKernel, $options),
                            $options,
                        ]));
                    } else {
                        $block[] = new Statement(new MethodCall('$kernel', 'registerService', [
                            new NewObject($class, []),
                            $options,
                        ]));
                    }
                }
            }
        }




        // Generate environment setup
        switch ($env) {
        case "production":
            $block[] = new Statement(new StaticMethodCall('Phifty\Environment\Production', 'init', [new Variable('$kernel')]));
            break;
        case "development":
        default:
            $block[] = new Statement(new StaticMethodCall('Phifty\Environment\Development', 'init', [new Variable('$kernel')]));
            break;
        }


        // BundleServiceProvider

        // Init bundle objects in the bootstrap.php script
        if ($bundleList) {
            foreach ($bundleList as $bundleName => $bundleConfig) {
                $bundleClass = "$bundleName\\$bundleName";
                if (class_exists($bundleClass, true)) {
                    $reflection = new ReflectionClass($bundleClass);
                    $bundleClassFile = $reflection->getFileName();
                } else {
                    $bundleClassFile = $bundleLoader->findBundleClass($bundleName);
                }
                if ($bundleClassFile) {
                    $block[] = new RequireStatement($bundleClassFile);
                }
            }
            foreach ($bundleList as $bundleName => $bundleConfig) {
                $bundleClass = "$bundleName\\$bundleName";
                $block[] = "\$kernel->bundles['$bundleName'] = $bundleClass::getInstance(\$kernel, " . var_export($bundleConfig,true) . ");";
            }
        }

        // $block[] = new Statement(new MethodCall('$kernel->bundles', 'init'));
        $block[] = new Statement(new MethodCall('$kernel', 'init'));

        if ($xhprof) {
            $block[] = '$xhprofNamespace = "phifty-bootstrap";';
            $block[] = '$xhprofData = xhprof_disable();';
            $block[] = '$xhprofRuns = new XHProfRuns_Default();';
            $block[] = '$runId = $xhprofRuns->save_run($xhprofData,$xhprofNamespace);';
            $block[] = 'header("X-XHPROF-RUN: $runId");';
            $block[] = 'header("X-XHPROF-NS: $xhprofNamespace");';
        }

        $this->logger->info("===> Compiling code to $outputFile");
        $code = $block->render();
        $this->logger->debug($code);
        return file_put_contents($outputFile, $code);
    }

    public function createConfigLoader($baseDir)
    {
        // We load other services from the definitions in config file
        // Simple load three config files (framework.yml, database.yml, application.yml)
        $loader = new ConfigLoader();
        if (file_exists($baseDir.'/config/framework.yml')) {
            $loader->load('framework', $baseDir.'/config/framework.yml');
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
