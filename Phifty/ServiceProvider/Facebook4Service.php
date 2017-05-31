<?php

namespace Phifty\ServiceProvider;

use Phifty\Kernel;
use Facebook\Facebook;
use Pimple\Container;
use Exception;

class Facebook4Service extends Container
{
    public function __construct(Kernel $kernel, array $options)
    {
        parent::__construct();
        $this['app_id'] = $options['AppId'];
        $this['session'] = function ($c) use ($options) {
            return new Facebook([
                'app_id'                => $options['AppId'],
                'app_secret'            => $options['AppSecret'],
                'default_graph_version' => $options['DefaultGraphVersion'],
            ]);
        };
        // make this factory?
        $this['redirect_login_helper'] = $this->factory(function ($c) {
            return $c['session']->getRedirectLoginHelper();
        });
        $this['login_url'] = $this->factory(function ($c) use ($options) {
            if (preg_match('#^https?://#', $options['DefaultLoginCallbackUrl'])) {
                $url = $options['DefaultLoginCallbackUrl'];
            } else {
                $url = $kernel->getBaseUrl() . $options['DefaultLoginCallbackUrl'];
            }
            $helper = $c['redirect_login_helper'];
            return $helper->getLoginUrl($url, $options['DefaultPermissions']);
        });
        $this['oauth2_client'] = $this->factory(function ($c) use ($options) {
            return $c['session']->getOAuth2Client();
            ;
        });
    }
}
