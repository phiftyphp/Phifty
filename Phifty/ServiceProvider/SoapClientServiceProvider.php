<?php

namespace Phifty\ServiceProvider;

use Exception;
use SoapClient;
use Phifty\Kernel;

class SoapClientServiceProvider extends BaseServiceProvider
{
    public function getId()
    {
        return 'SoapClient';
    }

    public function register(Kernel $kernel, array $options = array())
    {
        if (!isset($options['WSDL'])) {
            throw new Exception('WSDL is not defined.');
        }
        $kernel->soapClient = function () use ($options) {
            $wsdl = $options['WSDL'];
            if (!preg_match('#^https?://#', $wsdl) && $wsdl[0] != '/') {
                $wsdl = PH_APP_ROOT.DIRECTORY_SEPARATOR.$wsdl;
            }

            return new SoapClient($wsdl);
        };
    }
}
