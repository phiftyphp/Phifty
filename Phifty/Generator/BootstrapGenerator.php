<?php

namespace Phifty\Generator;

use ConfigKit\ConfigCompiler;
use ConfigKit\ConfigLoader;
use CodeGen\Generator\AppClassGenerator;
use CodeGen\UserClass;
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
use Universal\ClassLoader\ClassLoader;
use Universal\Container\ObjectContainer;

use Maghead\Runtime\Config\FileConfigLoader;

use Phifty\Bootstrap;
use Phifty\Generator\BootstrapGenerator;
use Phifty\Bundle\BundleLoader;
use Phifty\ServiceProvider\ServiceProvider;
use Phifty\ServiceProvider\BundleServiceProvider;
use Phifty\ServiceProvider\DatabaseServiceProvider;
use Phifty\ServiceProvider\ConfigServiceProvider;
use Phifty\ServiceProvider\BaseServiceProvider;
use Phifty\ServiceProvider\EventServiceProvider;
use Phifty\ComposerConfigBridge;
use Phifty\Kernel;
use Phifty\Utils;

use Phifty\Environment\Production;
use Phifty\Environment\Development;

class BootstrapGenerator
{
    protected $rootDir;

    protected $appDir;

    protected $frameworkRoot;

    protected $configLoader;

    protected $appNamespace = 'App';

    protected $appClassPrefix = 'App';

    protected $xhprofEnabled = false;

    protected $env;

    protected $xhprofConfig = [
        "namespace" => "phifty-bootstrap",
    ];

    public function __construct($rootDir, $frameworkRoot, $env, ConfigLoader $configLoader)
    {
        $this->rootDir = realpath($rootDir);
        $this->appDir = $rootDir . DIRECTORY_SEPARATOR . 'app';
        $this->frameworkRoot = $frameworkRoot;
        $this->env = $env;

        $this->configLoader = $configLoader;
    }

    public function enableXhprof(array $config = null)
    {
        if ($config) {
            $this->xhprofConfig = array_merge($this->xhprofConfig, $config);
        }
        return $this->xhprofEnabled = extension_loaded('xhprof');
    }

    public function generateAppConfigClass()
    {
        return AppConfigLoaderGenerator::generate($this->configLoader, $this->appNamespace, $this->appDir);
    }

    public function generateAppBaseKernelClass(Kernel $kernel)
    {
        return AppBaseKernelGenerator::generate($kernel, $this->appNamespace, $this->appDir);
    }

    public function generateAppKernelClass(Kernel $kernel)
    {
        return AppKernelGenerator::generate($kernel, $this->appNamespace, $this->appDir);
    }

    public function generateBootstrapHeader(Block $block)
    {
        $block[] = '<?php';
        $block[] = new CommentBlock([
            "This file is auto @generated through 'bin/phifty bootstrap' command.",
            "Don't modify this file directly",
            "",
            "For more information, please visit https://github.com/phifty-framework/Phifty",
        ]);


        if (extension_loaded('mbstring')) {
            $block[] = "mb_internal_encoding('UTF-8');";
        }
        if ($this->xhprofEnabled) {
            $block[] = 'xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);';
        }


        // Generate the require statements
        $block[] = 'global $kernel, $composerClassLoader, $psr4ClassLoader;';
        $block[] = new RequireClassStatement(ClassLoader::class);
        $block[] = new RequireClassStatement(Psr4ClassLoader::class);

        $block[] = new AssignStatement('$composerClassLoader', new RequireComposerAutoloadStatement([$this->rootDir]));

        // maghead loads these classes before everything
        // $block[] = new RequireClassStatement(\Universal\Event\EventDispatcher::class);
        // $block[] = new RequireClassStatement(\ConfigKit\ConfigLoader::class);
        // $block[] = new RequireClassStatement(\ConfigKit\ConfigCompiler::class);
        // $block[] = new RequireClassStatement(\ConfigKit\Accessor::class);

        $block[] = new RequireClassStatement(ObjectContainer::class);
        $block[] = new RequireClassStatement(Kernel::class);
        $block[] = new RequireClassStatement(Bootstrap::class);
        $block[] = new RequireClassStatement(ServiceProvider::class);
        $block[] = new RequireClassStatement(BaseServiceProvider::class);
        $block[] = new RequireClassStatement(\Phifty\Environment\Environment::class);
        $block[] = new RequireClassStatement(\Phifty\Bundle\BundleActionCreators::class);
        $block[] = new RequireClassStatement(\Phifty\Bundle::class);

        // Define global constants
        $block[] = new ConstStatement('PH_ROOT', $this->frameworkRoot);
        $block[] = new ConstStatement('PH_APP_ROOT', $this->rootDir);
        $block[] = new ConstStatement('DS', DIRECTORY_SEPARATOR);
        $block[] = new ConstStatement('PHIFTY_ENV', $this->env);
        $block[] = new DefineStatement('CLI', new Raw("PHP_SAPI === 'cli'"));

        // Generate Psr4 class loader section
        $block[] = new AssignStatement('$psr4ClassLoader', new NewObject(Psr4ClassLoader::class));
        $block[] = new Statement(new MethodCall('$psr4ClassLoader', 'register', [false]));
        $block[] = new Statement(new MethodCall('$psr4ClassLoader', 'addPrefix', [ 'App\\', $this->appDir . DIRECTORY_SEPARATOR ]));
    }

    public function generateBootstrapInitSection(Block $block)
    {
        // Generates: $configLoader = new \App\ConfigLoader;
        $block[] = new AssignStatement('$configLoader', new NewObject('App\\ConfigLoader'));

        // Generates: $kernel = new \App\Kernel($configLoader, $environment);
        // TODO: generate the environment name here.
        //
        // $this->environment  = $environment ?: getenv('PHIFTY_ENV') ?: self::DEV;
        $block[] = new AssignStatement('$env',  getenv('PHIFTY_ENV') ?: 'development' );
        $block[] = new AssignStatement('$kernel', new NewObject('App\\Kernel',[
            new Variable('$configLoader'),
            new Variable('$env'),
        ]));

        // Generate Core Serivces
        //
        // 1. ConfigServiceProvider
        // 2. EventServiceProvider

        // Generates: $kernel->registerServiceProvider(new \Phifty\ServiceProvider\ConfigServiceProvider($configLoader));
        $block[] = new RequireClassStatement(ConfigServiceProvider::class);
        $block[] = new Statement(new MethodCall('$kernel', 'registerServiceProvider', [
            new NewObject(ConfigServiceProvider::class, [ new Variable('$configLoader') ]),
        ]));

        // Load event service, so that we can bind events in Phifty
        // Generates: $kernel->registerServiceProvider(new \Phifty\ServiceProvider\EventServiceProvider());
        $block[] = new Comment("The event service is required for every component.");
        $block[] = new RequireClassStatement(EventServiceProvider::class);
        $block[] = new Statement(new MethodCall('$kernel', 'registerServiceProvider', [
            new NewObject(EventServiceProvider::class),
        ]));
    }

    public function generateBootstrapServiceProviderBlock(Block $block, Kernel $kernel)
    {
        if ($configServices = $this->configLoader->get('framework', 'ServiceProviders')) {
            // required objects: $kernel, $block
            $services = [];
            foreach ($configServices as $name => $options) {
                $serviceClass = \Maghead\Utils::resolveClass($name, ["App\\ServiceProvider","Phifty\\ServiceProvider"]);
                if (!$serviceClass) {
                    throw new LogicException("service class '$serviceClass' does not exist.");
                }
                $options = $serviceClass::canonicalizeConfig($kernel, $options ?: []);
                if ($options === null) {
                    throw new LogicException("$serviceClass::canonicalizeConfig should return an array for service config.");
                }
                $services[$serviceClass] = $options ?: [];
            }

            // Generate service provider statements
            foreach ($services as $serviceClass => $options) {
                $block[] = new RequireClassStatement($serviceClass);
                if (is_subclass_of($serviceClass, BaseServiceProvider::class)
                    && $serviceClass::Generatable($kernel, $options)) {
                    if ($stm = $serviceClass::generatePrepare($kernel, $options)) {
                        $block[] = $stm;
                    }
                    $block[] = new Statement(new MethodCall('$kernel', 'registerServiceProvider', [
                        $serviceClass::generateNew($kernel, $options),
                        $options,
                    ]));
                } else {
                    $block[] = new Statement(new MethodCall('$kernel', 'registerServiceProvider', [
                        new NewObject($serviceClass, []),
                        $options,
                    ]));
                }
            }
        }
    }



    public function generateBootstrapFooter(Block $block)
    {
        // Everything is ready, build app and run
        // $block[] = new Statement(new MethodCall('$kernel', 'init'));

        if ($this->xhprofEnabled) {
            $block[] = "\$xhprofNamespace = \"{$this->xhprofConfig['namespace']}\";";
            $block[] = "\$xhprofData = xhprof_disable();";

            $block[] = "\$xhprofRuns = new XHProfRuns_Default();";
            $block[] = "\$runId = \$xhprofRuns->save_run(\$xhprofData, \$xhprofNamespace);";

            $block[] = 'header("X-XHPROF-RUN: $runId");';
            $block[] = 'header("X-XHPROF-NS: $xhprofNamespace");';
        }
    }
}
