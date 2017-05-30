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


        $env = $this->options->env ?: getenv('PHIFTY_ENV') ?: 'development';
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

        $configLoader = Bootstrap::createConfigLoader($appRoot, getenv('PHIFTY_ENV'));

        $bGenerator = new BootstrapGenerator($appRoot, $frameworkRoot, $env, $configLoader);
        if ($this->options->xhprof) {
            $bGenerator->enableXhprof();
        }

        $this->logger->info("===> Generating config loader...");
        $appConfigClassPath = $bGenerator->generateAppConfigClass();

        // The runtime kernel will only contains "configLoader" and "classLoader" services
        $psr4ClassLoader = new Psr4ClassLoader;
        $runtimeKernel = Bootstrap::createKernel($configLoader, $psr4ClassLoader);
        $appKernelClassPath = $bGenerator->generateAppKernelClass($runtimeKernel);

        $this->logger->info("===> Generating bootstrap file: $outputFile");

        $block = new Block;
        $bGenerator->generateBootstrapHeader($block);

        $xhprof = extension_loaded('xhprof') && $this->options->xhprof;

        $block[] = new RequireStatement($appConfigClassPath);
        $block[] = new RequireStatement($appKernelClassPath);

        $bGenerator->generateBootstrapInitSection($block);

        // Kernel initialization after bootstrap script
        if ($configLoader->isLoaded('framework')) {
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
                $bundleClass = $bundleLoader->getBundleClass($bundleName);
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
                $bundleClass = $bundleLoader->getBundleClass($bundleName);
                $block[] = "\$kernel->bundles['$bundleName'] = $bundleClass::getInstance(\$kernel, " . var_export($bundleConfig, true) . ");";
            }
        }

        $bGenerator->generateBootstrapFooter($block);


        $this->logger->info("===> Compiling code to $outputFile");
        $code = $block->render();
        $this->logger->debug($code);
        return file_put_contents($outputFile, $code);
    }

}
