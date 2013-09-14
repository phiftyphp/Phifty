<?php
namespace Phifty\Testing;
use PHPUnit_Extensions_Selenium2TestCase;
use Exception;

abstract class Selenium2TestCase extends PHPUnit_Extensions_Selenium2TestCase
{

    /**
     * @var array environment configuration for selenium testing
     */
    public $environment;

    protected function setUp()
    {
        $kernel = kernel();

        if ( ! file_exists('config/testing.yml') ) {
            throw new Exception("config/testing.yml is not defined, please copy the config file from config/testing.dev.yml");
        }

        $kernel->config->load('testing','config/testing.yml');
        $config = $kernel->config->get('testing');
        if ($config && $config->Selenium) {
            if ($config->Selenium->Host)
                $this->setHost($config->Selenium->Host);

            if ($config->Selenium->Port)
                $this->setPort($config->Selenium->Port);

            if ($config->Selenium->Browser)
                $this->setBrowser($config->Selenium->Browser);

            if ($config->Environment)
                $this->environment = $config->Environment;

            $this->setBrowserUrl( $this->getBaseUrl() );
        }

        $this->setSeleniumServerRequestsTimeout(10);

        // XXX: SeleniumTestCase (1.0) seems don't support screenshotPath ?
        // $this->screenshotPath = $this->getScreenshotDir();
    }

    public function getBaseUrl()
    {
        $domain = kernel()->config->get('framework','Domain');

        return 'http://' . $domain;
    }

    // XXX: since we use screenshot test listener, we don't need this to get screenshots
    // Override the original method and tell Selenium to take screen shot when test fails
    public function onNotSuccessfulTest(\Exception $e)
    {
        // use unix-timestamp so that we can sort file by name
        if ( $this->takeScreenshot('last.png') === false
            || $this->takeScreenshot(
                str_replace('\\','_',get_class($this)) . '_' .
                str_replace('.','_',time(true)) . '.png' ) === false )
        {
            throw $e;
        }

        return parent::onNotSuccessfulTest($e);
    }

    public function getScreenshotDir()
    {
        return PH_ROOT . '/build/screenshots';
    }

    public function takeScreenshot($filename = null)
    {
        $image = $this->currentScreenshot();
        if ( !is_string( $image ) || ! $image ) {
            return false;
        }
        $path = $this->getScreenshotDir() . DIRECTORY_SEPARATOR . $filename;
        if ( file_put_contents( $path , $image ) === false ) {
            return false;
        }

        return true;
    }
}
