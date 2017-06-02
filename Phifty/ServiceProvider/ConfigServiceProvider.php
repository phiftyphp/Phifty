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

    public function register(Kernel $kernel, $options = array())
    {
        $self = $this;
        $kernel->config = function () use ($self) {
            return $self->loader;
        };
    }

    public function load($section, $file)
    {
        return $this->loader->load($section, $file);
    }
}
