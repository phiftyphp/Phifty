<?php
namespace Phifty\Service;
use Phifty\Config\ConfigLoader;

/**
 * Config service usage
 *
 * $noreply = kernel()->config->get('framework','Mail.NoReply');
 *
 * $domain = kernel()->config->framework->Domain ?: $_SERVER['HTTP_HOST'];
 *
 * $config = $kernel->config->get('framework','Locale');
 */
class ConfigService
    implements ServiceInterface
{

    public $loader;

    public function __construct( $loader = null )
    {
        $this->loader = $loader ?: new ConfigLoader;
    }

    public function getId() { return 'config'; }

    public function register($kernel, $options = array() )
    {
        $self = $this;
        $kernel->config = function() use ($self) {
            return $self->loader;
        };
    }

    public function load($section,$file)
    {
        return $this->loader->load($section,$file);
    }
}
