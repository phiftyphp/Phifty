<?php

namespace Phifty\Generator;

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
        $generator = new AppClassGenerator([ 'namespace' => $this->appNamespace, 'prefix' => $this->appClassPrefix ]);
        $class = $generator->generate($this->configLoader);
        return $class->generatePsr4ClassUnder($this->appDir);
    }

    public function generateAppKernelClass(Kernel $kernel)
    {
        $generator = new AppClassGenerator([
            'namespace' => $this->appNamespace,
            'prefix' => $this->appClassPrefix,
            'property_filter' => function ($property) {
                return !preg_match('/^(applications|services|environment|isDev|_.*)$/i', $property->getName());
            }
        ]);
        $class = $generator->generate($kernel);
        return $class->generatePsr4ClassUnder($this->appDir);
    }

    public function generateBootstrapHeader(Block $block)
    {
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

        $block[] = new ConstStatement('PH_ROOT', $this->frameworkRoot);
        $block[] = new ConstStatement('PH_APP_ROOT', $this->rootDir);
        $block[] = new ConstStatement('DS', DIRECTORY_SEPARATOR);


        if ($this->xhprofEnabled) {
            $block[] = 'xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);';
        }

        $block[] = new ConstStatement('PH_ENV', $this->env);

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
            'App\\', $this->appDir . DIRECTORY_SEPARATOR ]));

        // Generate Spl Class loader section
        $block[] = new RequireClassStatement(SplClassLoader::class);
        $block[] = '$splClassLoader = new \Universal\ClassLoader\SplClassLoader();';
        $block[] = '$splClassLoader->useIncludePath(false);';
        $block[] = '$splClassLoader->register(false);';

        $block[] = new RequireClassStatement(ObjectContainer::class);
        $block[] = new RequireClassStatement(Kernel::class);
        $block[] = new RequireClassStatement(Bootstrap::class);
    }

    public function generateBootstrapInitSection(Block $block)
    {
        // Generates: $kernel = new \App\AppKernel;
        $block[] = new AssignStatement('$kernel', new NewObject('App\\AppKernel'));

        // Generates: $kernel->registerService(new \Phifty\ServiceProvider\ClassLoaderServiceProvider($splClassLoader));
        $block[] = new Statement(new MethodCall('$kernel', 'registerService', [
            new NewObject('\\Phifty\\ServiceProvider\\ClassLoaderServiceProvider', [ new Variable('$splClassLoader') ]),
        ]));

        // Generates: $configLoader = new \App\AppConfigLoader;
        $block[] = new AssignStatement('$configLoader', new NewObject('App\\AppConfigLoader'));


        // Generate Core Serivces
        //
        // 1. ConfigServiceProvider
        // 2. EventServiceProvider

        // Generates: $kernel->registerService(new \Phifty\ServiceProvider\ConfigServiceProvider($configLoader));
        $block[] = new RequireClassStatement(ConfigServiceProvider::class);
        $block[] = new Statement(new MethodCall('$kernel', 'registerService', [
            new NewObject(ConfigServiceProvider::class, [ new Variable('$configLoader') ]),
        ]));

        // Load event service, so that we can bind events in Phifty
        // Generates: $kernel->registerService(new \Phifty\ServiceProvider\EventServiceProvider());
        $block[] = new Comment("The event service is required for every component.");
        $block[] = new RequireClassStatement(EventServiceProvider::class);
        $block[] = new Statement(new MethodCall('$kernel', 'registerService', [
            new NewObject(EventServiceProvider::class),
        ]));
    }

    public function generateBootstrapFooter(Block $block)
    {
        if ($this->xhprofEnabled) {
            $block[] = "$xhprofNamespace = \"{$this->xhprofConfig['namespace']}\";";
            $block[] = '$xhprofData = xhprof_disable();';

            $block[] = '$xhprofRuns = new XHProfRuns_Default();';
            $block[] = '$runId = $xhprofRuns->save_run($xhprofData, $xhprofNamespace);';
            $block[] = 'header("X-XHPROF-RUN: $runId");';
            $block[] = 'header("X-XHPROF-NS: $xhprofNamespace");';
        }
    }

}
