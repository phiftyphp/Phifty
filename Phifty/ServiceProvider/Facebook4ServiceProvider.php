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
class Facebook4ServiceProvider extends BaseServiceProvider
{
    public function getId()
    {
        return 'facebook4';
    }

    public function register(Kernel $kernel, $options = array())
    {
        $kernel->facebook = function () use ($options, $kernel) {
            return new Facebook4Service($kernel, $options);
        };
    }

    public static function canonicalizeConfig(Kernel $kernel, array $options)
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

    public function getComposerRequire()
    {
        return ["facebook/php-sdk-v4" => "~5.0"];
    }
}
