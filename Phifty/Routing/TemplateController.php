<?php

namespace Phifty\Routing;

use Phifty\Controller;

class TemplateController extends Controller
{
    protected $template;

    protected $args;

    public function context(array & $environment, array $response)
    {
        parent::context($environment, $response);
        list($pcre, $path, $callback, $options) = $this->matchedRoute; // the structure is from Pux
        $args = $options['args'];
        $this->template = $args['template'];
        $this->args = isset($args['template_args']) ? $args['template_args'] : [];
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
