<?php

namespace Phifty\ServiceProvider;

use Phifty\Kernel;

class ViewServiceProvider extends ServiceProvider
{
    public function getId()
    {
        return 'view';
    }

    public static function canonicalizeConfig(Kernel $k, array $options)
    {
        if (!isset($options['Class'])) {
            $options['Class'] = \Phifty\View::class;
        }
        return $options;
    }

    public function register(Kernel $k, array $options = array())
    {
        $k->factory('view', function (Kernel $k, $viewClass = null) use ($options) {
            $viewClass = $viewClass ?: $options['Class'];
            return new $viewClass($k);
        });
    }
}
