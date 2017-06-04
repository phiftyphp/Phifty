<?php

namespace Phifty\Routing;

use Pux\Controller\Controller;

class TemplateController extends Controller
{
    protected $template;

    protected $args;

    public function __construct(array $environment = array(), array $response = array(), array $matchedRoute = array())
    {
        parent::__construct($environment, $response, $matchedRoute);

        list($pcre, $path, $callback, $options) = $matchedRoute; // the structure is from Pux

        $args = $options['args'];
        $this->template = $args['template'];
        $this->args = isset($args['template_args']) ? $args['template_args'] : array();
    }

    public function templateAction()
    {
        $view       = $this->kernel->view;
        if ($this->args) {
            $view->assign($this->args);
        }
        return $view->render($this->template);
    }
}
