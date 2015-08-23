<?php
namespace Phifty\ServiceProvider;
use Phifty\ServiceProvider\ServiceProvider;

class CurrentUserServiceProvider
    implements ServiceProvider
{

    public function getId() { return 'current_user'; }

    public function register($kernel,$options = array() )
    {

        $kernel->event->register('view.init', function($view) {
            $view->args['CurrentUser'] = kernel()->currentUser;
        });
        // current user builder
        $kernel->currentUser = function() use ($kernel,$options) {
            // framework.CurrentUser.Class is for backward compatible.
            $args = array();

            $args['model_class'] = isset($options['Model'])
                ? $options['Model']
                : $kernel->config->get('framework','CurrentUser.Model');

            if ( isset($args['PrimaryKey']) ) {
                $args['primary_key'] = $args['PrimaryKey'];
            }

            if ( isset($args['SessionPrefix']) ) {
                $args['session_prefix'] = $args['SessionPrefix'];
            }

            $currentUserClass = isset($options['Class'])
                ? $options['Class']
                : $kernel->config->get('framework','CurrentUser.Class') ?: 'Phifty\Security\CurrentUser';

            return new $currentUserClass($args);
        };
    }
}
