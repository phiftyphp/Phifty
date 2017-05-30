<?php

namespace Phifty\Console\Command;

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
use Universal\ClassLoader\Psr4ClassLoader;
use Universal\ClassLoader\SplClassLoader;
use Universal\Container\ObjectContainer;

use Maghead\Runtime\Config\FileConfigLoader;

use Phifty\Bootstrap;
use Phifty\Generator\BootstrapGenerator;
use Phifty\Bundle\BundleLoader;
use Phifty\ServiceProvider\BundleServiceProvider;
use Phifty\ServiceProvider\DatabaseServiceProvider;
use Phifty\ServiceProvider\ConfigServiceProvider;
use Phifty\ServiceProvider\EventServiceProvider;
use Phifty\Kernel;
use Phifty\Utils;

use Phifty\Environment\Production;
use Phifty\Environment\Development;

use ReflectionClass;
use Exception;
use LogicException;



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
     * To generate the bootstrap script, we need to prepare few things:
     *
     *  1. class loader (can be loaded from composer autoloader)
     *  2. config loader
     *
     * Build command generates the bootstrap.php script in the following sections:
     *
     *  ----------------------------
     *  Predefined Constants
     *  ----------------------------
     *  Global variable definitions
     *  ----------------------------
     *  Require & init class loaders
     *  ----------------------------
     *  Create config loader object
     *  ----------------------------
     *  Create Kernel object
     *  ----------------------------
     *  Register ServiceProviders
     *  ----------------------------
     *  Register Apps
     *  ----------------------------
     *  Register Bundles
     *  ----------------------------
     *  Init Kernel object
     *  ----------------------------
     */
    public function execute()
    {
        // TODO: connect to differnt config by using environment variable (PHIFTY_ENV)
        $this->logger->info("===> Building config files...");
        $configPaths = Utils::find_framework_config(getcwd());
        Utils::compile_framework_configs($configPaths);


        $outputFile = $this->options->output;


        $frameworkRoot = dirname(dirname(dirname(__DIR__)));
        $appRoot = getcwd();
        $appDirectory = $appRoot . DIRECTORY_SEPARATOR . 'app';

        defined('PH_APP_ROOT') || define('PH_APP_ROOT', $appRoot);
        defined('PH_ROOT') || define('PH_ROOT', $frameworkRoot);

        $this->logger->info('Application root directory:' . $appRoot);

        if ($this->options->clean) {
            $this->logger->info("Removing genereated files");
            Utils::unlink_files([
                $outputFile,
                $appDirectory . DIRECTORY_SEPARATOR . 'AppKernel.php',
                $appDirectory . DIRECTORY_SEPARATOR . 'AppConfigLoader.php',
            ]);
            $this->logger->info('Cached files are cleaned up');
            return;
        }



        $this->logger->info("===> Generating config loader...");
        // generating the config loader
        $configLoader = Bootstrap::createConfigLoader($appRoot, getenv('PHIFTY_ENV'));

        $bGenerator = new BootstrapGenerator($appRoot, $configLoader);
        $appConfigClassPath = $bGenerator->generateAppConfigClass('App', 'App');
        require_once $appConfigClassPath;


        // The runtime kernel will only contains "configLoader" and "classLoader" services
        $psr4ClassLoader = new Psr4ClassLoader;
        $runtimeKernel = Bootstrap::createKernel($configLoader, $psr4ClassLoader);
        $appKernelClassPath = $bGenerator->generateAppKernelClass($runtimeKernel);
        require_once $appKernelClassPath;





        $this->logger->info("===> Generating bootstrap file: $outputFile");

        $block = new Block;
        $block[] = '<?php';
        $block[] = new CommentBlock([
            "This file is auto @generated through 'bin/phifty bootstrap' command.",
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
        $block[] = new ConstStatement('PH_ROOT', $frameworkRoot);
        $block[] = new ConstStatement('PH_APP_ROOT', $appRoot);
        $block[] = new ConstStatement('DS', DIRECTORY_SEPARATOR);

        $env = $this->options->env ?: getenv('PHIFTY_ENV') ?: 'development';
        $block[] = new ConstStatement('PH_ENV', $env);

        // CLI mode should be dynamic
        $block[] = new DefineStatement('CLI', new Raw("isset(\$_SERVER['argc']) && !isset(\$_SERVER['HTTP_HOST'])"));
        $block[] = new DefineStatement('CLI_MODE', new Raw("CLI"));

        $block[] = 'global $kernel, $composerClassLoader, $psr4ClassLoader, $splClassLoader;';
        $block[] = new AssignStatement('$composerClassLoader', new RequireComposerAutoloadStatement());


        // Generate Psr4 class loader section
        $block[] = new RequireClassStatement(Psr4ClassLoader::class);
        $block[] = '$psr4ClassLoader = new \Universal\ClassLoader\Psr4ClassLoader();';
        $block[] = '$psr4ClassLoader->register(false);';

        $block[] = new Statement(new MethodCall('$psr4ClassLoader', 'addPrefix', [
            'App\\', $appDirectory . DIRECTORY_SEPARATOR ]));

        // Generate Spl Class loader section
        $block[] = new RequireClassStatement(SplClassLoader::class);
        $block[] = '$splClassLoader = new \Universal\ClassLoader\SplClassLoader();';
        $block[] = '$splClassLoader->useIncludePath(false);';
        $block[] = '$splClassLoader->register(false);';

        $block[] = new RequireClassStatement(ObjectContainer::class);
        $block[] = new RequireClassStatement(Kernel::class);

        $block[] = new RequireStatement($appConfigClassPath);
        $block[] = new RequireStatement($appKernelClassPath);

        // Generates: $kernel = new \App\AppKernel;
        $block[] = new AssignStatement('$kernel', new NewObject('App\\AppKernel'));

        // Generates: $kernel->registerService(new \Phifty\ServiceProvider\ClassLoaderServiceProvider($splClassLoader));
        $block[] = new Statement(new MethodCall('$kernel', 'registerService', [
            new NewObject('\\Phifty\\ServiceProvider\\ClassLoaderServiceProvider', [ new Variable('$splClassLoader') ]),
        ]));


        // Generates: $configLoader = new \App\AppConfigLoader;
        $block[] = new AssignStatement('$configLoader', new NewObject('App\\AppConfigLoader'));

        // Generates: $kernel->registerService(new \Phifty\ServiceProvider\ConfigServiceProvider($configLoader));
        $block[] = new RequireClassStatement(ConfigServiceProvider::class);
        $block[] = new Statement(new MethodCall('$kernel', 'registerService', [
            new NewObject(ConfigServiceProvider::class, [ new Variable('$configLoader') ]),
        ]));

        // load event service, so that we can bind events in Phifty
        // Generates: $kernel->registerService(new \Phifty\ServiceProvider\EventServiceProvider());
        $block[] = new Comment("The event service is required for every component.");
        $block[] = new RequireClassStatement(EventServiceProvider::class);
        $block[] = new Statement(new MethodCall('$kernel', 'registerService', [
            new NewObject(EventServiceProvider::class),
        ]));

        // Include bootstrap class
        $block[] = new RequireStatement(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'Bootstrap.php');

        // Kernel initialization after bootstrap script
        if ($configLoader->isLoaded('framework')) {

            if (is_dir($appDirectory)) {
                $block[] = new Statement(new MethodCall('$psr4ClassLoader', 'addPrefix', [
                        'App\\', [$appDirectory . DIRECTORY_SEPARATOR],
                ]));
            }

            if ($services = $configLoader->get('framework', 'ServiceProviders')) {
                foreach ($services as $name => $options) {
                    if (!$options) {
                        $options = [];
                    }

                    $serviceClass = \Maghead\Utils::resolveClass($name, ["App\\ServiceProvider","Phifty\\ServiceProvider"]);
                    if (!$serviceClass) {
                        throw new LogicException("service class '$serviceClass' does not exist.");
                    }
                    $block[] = new RequireClassStatement($serviceClass);

                    $this->logger->info("Generating registration for $serviceClass ...");

                    $options = $serviceClass::canonicalizeConfig($runtimeKernel, $options);
                    if ($options === null) {
                        throw new LogicException("$serviceClass::canonicalizeConfig should return an array for service config.");
                    }

                    if (is_subclass_of($serviceClass, 'Phifty\\ServiceProvider\\BaseServiceProvider')
                        && $serviceClass::Generatable($runtimeKernel, $options)) {
                        if ($stm = $serviceClass::generatePrepare($runtimeKernel, $options)) {
                            $block[] = $stm;
                        }
                        $block[] = new Statement(new MethodCall('$kernel', 'registerService', [
                            $serviceClass::generateNew($runtimeKernel, $options),
                            $options,
                        ]));
                    } else {
                        $block[] = new Statement(new MethodCall('$kernel', 'registerService', [
                            new NewObject($serviceClass, []),
                            $options,
                        ]));
                    }
                }
            }
        }




        // Generate environment setup
        switch ($env) {
        case "production":
            $block[] = new Statement(new StaticMethodCall(Production::class, 'init', [new Variable('$kernel')]));
            break;
        case "development":
        default:
            $block[] = new Statement(new StaticMethodCall(Development::class, 'init', [new Variable('$kernel')]));
            break;
        }

        // Generate script for initializing the bundle objects in the
        // bootstrap.php script
        $bundleLoaderConfig = $configLoader->get('framework', 'BundleLoader') ?: new \ConfigKit\Accessor([ 'Paths' => ['app_bundles','bundles'] ]);
        $bundleLoader = new BundleLoader($runtimeKernel, $bundleLoaderConfig['Paths']->toArray());
        $bundleList = $configLoader->get('framework', 'Bundles');
        if ($bundleList) {

            $bundlePrefixes = $bundleLoader->getBundlePrefixes($bundleList);
            foreach ($bundlePrefixes as $prefix => $path) {
                $block[] = new Statement(new MethodCall('$psr4ClassLoader', 'addPrefix', [$prefix, $path]));
            }

            foreach ($bundleList as $bundleName => $bundleConfig) {
                $bundleClass = "$bundleName\\$bundleName";
                if (class_exists($bundleClass, true)) {
                    $reflection = new ReflectionClass($bundleClass);
                    $bundleClassFile = $reflection->getFileName();
                } else {
                    $bundleClassFile = $bundleLoader->findBundleClassFile($bundleName);
                }
                if ($bundleClassFile) {
                    $block[] = new RequireStatement($bundleClassFile);
                }
            }
            foreach ($bundleList as $bundleName => $bundleConfig) {
                $bundleClass = "$bundleName\\$bundleName";
                $block[] = "\$kernel->bundles['$bundleName'] = $bundleClass::getInstance(\$kernel, " . var_export($bundleConfig, true) . ");";
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

}
