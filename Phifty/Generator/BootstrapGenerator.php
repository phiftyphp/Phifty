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

use Maghead\Runtime\Config\FileConfigLoader;

use Phifty\Bundle\BundleLoader;
use Phifty\ServiceProvider\BundleServiceProvider;
use Phifty\Kernel;

class BootstrapGenerator
{
    protected $rootDir;

    protected $appDir;

    protected $configLoader;

    protected $appNamespace = 'App';

    protected $appClassPrefix = 'App';

    public function __construct($rootDir, ConfigLoader $configLoader)
    {
        $this->rootDir = realpath($rootDir);
        $this->appDir = $rootDir . DIRECTORY_SEPARATOR . 'app';

        $this->configLoader = $configLoader;
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
}
