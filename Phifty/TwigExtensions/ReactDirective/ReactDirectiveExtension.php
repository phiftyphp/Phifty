<?php
namespace Phifty\TwigExtensions\ReactDirective;
use Twig_Extension;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\AssetRender;
use AssetKit\AssetCompiler;

class ReactDirectiveExtension extends Twig_Extension
{
    public function __construct()
    {
    }

    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return array An array of Twig_TokenParserInterface or Twig_TokenParserBrokerInterface instances
     */
    public function getTokenParsers()
    {
        return array(new ReactDirectiveParser());
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName() {
        return 'ReactDirective';
    }
}

