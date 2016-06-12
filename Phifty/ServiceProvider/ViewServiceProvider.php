<?php

namespace Phifty\ServiceProvider;

use Phifty\Kernel;

/**
 * Usage:.
 *
 *    $view = kernel()->view;
 */
class ViewFactory
{
    protected $kernel;

    protected $options = array();

    public function __construct(Kernel $kernel, array $options = array())
    {
        $this->kernel = $kernel;
        $this->options = $options;
    }

    public function __invoke($class = null)
    {
        $viewClass = $class ?: $this->options['Class'];
        return new $viewClass($this->kernel, [ /* unused option array */ ]);
    }
}

class ViewServiceProvider extends BaseServiceProvider
{
    public function getId()
    {
        return 'View';
    }

    public static function canonicalizeConfig(Kernel $kernel, array $options)
    {
        if (!isset($options['Class'])) {
            $options['Class'] = 'Phifty\\View';
        }
        return $options;
    }

    public function register(Kernel $kernel, $options = array())
    {
        $kernel->registerFactory('view', new ViewFactory($kernel, $options));
    }
}
