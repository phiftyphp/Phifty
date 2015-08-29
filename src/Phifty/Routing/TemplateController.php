<?php
namespace Phifty\Routing;
use Phifty\View\Engine;
use Pux\Controller\Controller;

class TemplateController extends Controller
{
    public $template;

    public $args = array();

    public function __construct(array $environment, array $matchedRoute)
    {
        parent::__construct($environment, $matchedRoute);
        list($pcre, $path, $callback, $options ) = $matchedRoute;

        $args = $options['args'];
        $this->template = $args['template'];
        $this->args = isset($args['template_args']) ? $args['template_args'] : array();
    }

    public function run()
    {
        $template   = $this->template;
        $args       = $this->args;

# Get config from framework.yml
#  View:
#    Backend: twig
#    Class: \Phifty\View
#    TemplateDirs: {  }
        $engineType = kernel()->config->get('framework','View.Backend') ?: 'twig';

        /* get template engine */
        $engine = Engine::createEngine( $engineType );
        $viewClass = kernel()->config->get('framework','View.Class') ?: 'Phifty\View';
        $view = new $viewClass( $engine );
        if ($args) {
            $view->assign( $args );
        }

        return $view->render( $template );
    }
}
