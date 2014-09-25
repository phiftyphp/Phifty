<?php
namespace Phifty\Service;
use Exception;
use SoapClient;

class SoapClientService
    implements ServiceRegister
{
    public function getId() { return 'SoapClient'; }

    public function register($kernel, $options = array() )
    {
        if ( ! isset($options["WSDL"]) ) {
            throw new Exception("WSDL is not defined.");
        }
        $kernel->soapClient = function() use ($options) {
            $wsdl = $options['WSDL'];
            if ( ! preg_match('#^https?://#', $wsdl) && $wsdl[0] != '/' ) {
                $wsdl = PH_APP_ROOT . DIRECTORY_SEPARATOR . $wsdl;
            }
            return new SoapClient( $wsdl );
        };
    }
}

