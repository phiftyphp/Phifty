<?php
namespace Phifty\View;
use Exception;

class Heredoc
{
    public $engine;
    public $content;

    public function __construct($engineType)
    {
        $this->engine = \Phifty\View\Engine::createEngine( $engineType );
    }

    public function render($args = array() )
    {
        if ( ! $this->content )
            throw new Exception( 'template content is not defined.' );

        return $this->engine->renderString( $this->content , $args );
    }
}

/*
$heredoc = new Heredoc('twig');
$heredoc->content =<<<END;

END
$heredoc->render();

 */
