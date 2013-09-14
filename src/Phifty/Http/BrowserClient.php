<?php
namespace Phifty\Http;
use Phifty\Http\Browscap;

/**
 * Debian system:
 *
 * $ apt-get install geoip-bin geoip-database libgeoip-dev libgeoip1 php5-geoip
 */
class BrowserClient
{

    /**
     * @var string Client IP address
     */
    public $ip;

    /**
     * @var string Client host name
     */
    public $host;

    /**
     * AS for Asia
     * EU for Europe
     * SA for South America
     * AF for Africa
     * AN for µAntartica
     * OC for Oceania
     * NA for North America
     */
    public $continent;

    public $countryCode;

    /**
     * @var string Country name, only available when geoip extension is enabled.
     */
    public $country;

    /**
     * @var string City name, only available when geoip extension is enabled.
     */
    public $city;

    /**
     * @var string latitude
     */
    public $latitude;

    public $longitude;

    public $geoipSupports = false;

    /**
     * @var string User agent string
     */
    public $userAgent;

    /**
     * @var string Referer
     */
    public $referer;

    /**
     * @var array browser info array
     */
    public $browser = array();

    /**
     *
     * @param string $ip           user ip address
     * @param string $userAgentStr user agent string
     */
    public function __construct($ip = null, $userAgentStr = null)
    {
        $this->ip = $ip ? $ip : $this->getIp();

        if ($userAgentStr) {
            $this->userAgent = $userAgentStr;
        } elseif ( isset($_SERVER['HTTP_USER_AGENT']) ) {
            $this->userAgent = $_SERVER['HTTP_USER_AGENT'];
        }

        $this->host = $this->getHostname();

        if ( isset($_SERVER['HTTP_REFERER']) ) {
            $this->referer = $_SERVER['HTTP_REFERER'];
        }

        // get extended informations
        if ( extension_loaded('geoip') ) {
            $this->geoipSupports = true;
        }

        if ($this->ip && $this->geoipSupports) {
            if ( $record = @geoip_record_by_name($this->ip) ) {
                $this->continent     = @$record['continent_code'];
                $this->countryCode   = @$record['country_code'];
                $this->country       = @$record['country_name'];
                $this->city          = @$record['city'];
                $this->latitude      = @$record['latitude'];
                $this->longitude     = @$record['longitude'];
            }
        }

        // if browscap string is set in php.ini, we can use get_browser function
        if ( $browscapStr = ini_get('browscap') ) {
            $this->browser = (object) get_browser( $userAgentStr , true);
        } else {
            // $browscap = new Browscap( kernel()->cacheDir );
            // $this->browser = (object) $browscap->getBrowser( $userAgentStr , true);
        }
    }

    public function getIp()
    {
        if ( isset( $_SERVER['HTTP_CLIENT_IP']) ) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif ( isset($_SERVER['REMOTE_ADDR']) ) {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    public function getHostname()
    {
        if ( $this->ip && function_exists('gethostbyaddr') ) {
            return gethostbyaddr( $this->ip );
        }
    }

}
