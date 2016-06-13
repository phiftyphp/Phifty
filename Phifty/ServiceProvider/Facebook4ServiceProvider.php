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
        $kernel->facebook = function() use ($options, $kernel) {
            $container = new Container;
            $container['app_id'] = $options['AppId'];
            $container['session'] = function($c) use ($options) {
                return new Facebook([
                    'app_id'                => $options['AppId'],
                    'app_secret'            => $options['AppSecret'],
                    'default_graph_version' => $options['DefaultGraphVersion'],
                ]);
            };
            // make this factory?
            $container['redirect_login_helper'] = $container->factory(function($c) {
                return $c['session']->getRedirectLoginHelper();
            });
            $container['login_url'] = $container->factory(function($c) use ($options) {
                if (preg_match('#^https?://#',$options['DefaultLoginCallbackUrl'])) {
                    $url = $options['DefaultLoginCallbackUrl'];
                } else {
                    $url = $kernel->getBaseUrl() . $options['DefaultLoginCallbackUrl'];
                }
                $helper = $c['redirect_login_helper'];
                return $helper->getLoginUrl($url, $options['DefaultPermissions']);
            });
            $container['oauth2_client'] = $container->factory(function($c) use ($options) {
                return $c['session']->getOAuth2Client();;
            });
            return $container;
        };
    }

    static public function canonicalizeConfig(Kernel $kernel, array $options)
    {
        if (!isset($options['AppId']) || !isset($options['AppSecret'])) {
            throw new Exception('AppId or AppSecret is not defined.');
        }
        if (!isset($options['DefaultGraphVersion'])) {
            $options['DefaultGraphVersion'] = 'v2.5';
        }
        if (!isset($options['DefaultPermissions'])) {
            $options['DefaultPermissions'] = ['public_profile','email'];
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
