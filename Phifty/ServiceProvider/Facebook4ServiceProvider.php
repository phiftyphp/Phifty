<?php

namespace Phifty\ServiceProvider;

use Phifty\Kernel;
use Facebook\FacebookSession;
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
