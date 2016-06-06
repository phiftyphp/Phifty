<?php
namespace Phifty\ServiceProvider;
use Phifty\ServiceProvider\ServiceProvider;
use CodeGen\Expr\NewObject;
use Phifty\Kernel;

class CurrentUserServiceProvider extends BaseServiceProvider
{

    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }


    public function getId() { return 'current_user'; }

    static public function canonicalizeConfig(Kernel $kernel, array $options)
    {
        $args = [];
        $args['model_class'] = isset($options['Model'])
            ? $options['Model']
            : $kernel->config->get('framework','CurrentUser.Model');

        if (isset($options['PrimaryKey']) ) {
            $args['primary_key'] = $options['PrimaryKey'];
        }

        if (isset($options['SessionPrefix']) ) {
            $args['session_prefix'] = $options['SessionPrefix'];
        }

        $currentUserClass = isset($options['Class'])
            ? $options['Class']
            : $kernel->config->get('framework','CurrentUser.Class') ?: 'Phifty\Security\CurrentUser';

        $options['CurrentUserConstructorArgs'] = $args;
        $options['CurrentUserClass'] = $currentUserClass;
        return $options;
    }

    public function register($kernel, $options = array() )
    {
        $kernel->event->register('view.init', function($view) use ($kernel) {
            $view['CurrentUser'] = $kernel->currentUser;
        });
        $kernel->currentUser = function() use ($options) {
            $currentUserClass = $options['CurrentUserClass'];
            return new $currentUserClass($options['CurrentUserConstructorArgs']);
        };
    }
}
