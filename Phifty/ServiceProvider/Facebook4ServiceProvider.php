<?php

namespace Phifty\ServiceProvider;

use Phifty\Kernel;
use Facebook\Facebook;
use Pimple\Container;
use Exception;

/*
 * The official facebook service provider
 * Facebook4ServiceProvider:
 *   AppId: {appId}
 *   AppSecret: {app secret}
 */
class Facebook4ServiceProvider
    extends BaseServiceProvider
{
    public function getId()
    {
        return 'Facebook';
    }

    public function register(Kernel $kernel, $options = array())
    {
        FacebookSession::setDefaultApplication($options['AppId'], $options['AppSecret']);

        $kernel->facebook = function() use ($options) {
            $container = new Container;
            $container['session'] = function($c) use ($options) {
                return new Facebook([
                    'app_id'                => $options['AppId']
                    'app_secret'            => $options['AppSecret'],
                    'default_graph_version' => $options['DefaultGraphVersion'],
                ]);
            };
            $container['login_helper'] = function($c) {
                return $c['session']->getRedirectLoginHelper();
            };
            /*
            $permissions = ['email', 'user_likes']; // optional
            return $helper->getLoginUrl('http://{your-website}/login-callback.php', $permissions);
            */
            return $container;
        };

        $kernel->facebookSession = function () use ($options) {
            return FacebookSession::newAppSession();
        };
    }

    public function canonicalizeConfig(Kernel $kernel, array $options)
    {
        if (!isset($options['AppId']) || !isset($options['AppSecret'])) {
            throw new Exception('AppId or AppSecret is not defined.');
        }
        if (!isset($options['DefaultGraphVersion'])) {
            $options['DefaultGraphVersion'] = 'v2.5';
        }
        return $options;
    }

    public function getComposerDependency()
    {
        return ["facebook/php-sdk-v4" => "~5.0"];
    }
}
