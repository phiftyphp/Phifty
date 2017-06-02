<?php

namespace Phifty\ServiceProvider;

use Phifty\Kernel;
use ConfigKit\ConfigLoader;

/**
 * Config service usage.
 *
 * $noreply = kernel()->config->get('framework','Mail.NoReply');
 *
 * $domain = kernel()->config->framework->Domain ?: $_SERVER['HTTP_HOST'];
 *
 * $config = $kernel->config->get('framework','Locale');
 */
class ConfigServiceProvider extends BaseServiceProvider
{
    protected $loader;

    public function __construct(ConfigLoader $loader)
    {
        $this->loader = $loader;
    }

    public function getId()
    {
        return 'config';
    }

    public function register(Kernel $kernel, array $options = array())
    {
        $self = $this;
        $kernel->config = $this->loader;
    }
}
