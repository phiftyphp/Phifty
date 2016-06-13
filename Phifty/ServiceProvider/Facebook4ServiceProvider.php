<?php

namespace Phifty\ServiceProvider;

use Exception;
use Facebook\FacebookSession;
use Phifty\Kernel;

/*
 * The official facebook service provider
 * Facebook4ServiceProvider:
 *   AppId: {appId}
 *   AppSecret: {app secret}
 */
class Facebook4ServiceProvider 
    extends BaseServiceProvider 
    implements ServiceOptionValidator
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

    public function validateOptions($options = array())
    {
        if (!isset($options['AppId']) || !isset($options['AppSecret'])) {
            throw new Exception('AppId or AppSecret is not defined.');
        }
    }

    public function getComposerDependency()
    {
        return ["facebook/php-sdk-v4" => "~5.0"];
    }
}
