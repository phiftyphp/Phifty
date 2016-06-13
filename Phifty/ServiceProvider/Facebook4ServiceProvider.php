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
 *   DefaultPermissions: ['email']
 *   DefaultLoginCallbackUrl: '/oauth/facebook/callback'
 */
class Facebook4ServiceProvider
    extends BaseServiceProvider
{
    public function getId()
    {
        return 'facebook4';
    }

    public function register(Kernel $kernel, $options = array())
    {
        FacebookSession::setDefaultApplication($options['AppId'], $options['AppSecret']);

        $kernel->facebook = function() use ($options, $kernel) {
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
            $container['login_url'] = $c->factory(function($c) use ($options) {
                if (preg_match('#^https?://#',$options['DefaultLoginCallbackUrl'])) {
                    $url = $options['DefaultLoginCallbackUrl'];
                } else {
                    $url = $kernel->getBaseUrl() . $options['DefaultLoginCallbackUrl'];
                }
                return $helper->getLoginUrl($url, $options['DefaultPermissions']);
            });
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
        if (!isset($options['DefaultPermissions'])) {
            $options['DefaultPermissions'] = ['email'];
        }
        if (!isset($options['DefaultLoginCallback'])) {
            $options['DefaultLoginCallbackUrl'] = '/oauth/facebook/callback';
        }
        return $options;
    }

    public function getComposerDependency()
    {
        return ["facebook/php-sdk-v4" => "~5.0"];
    }
}
