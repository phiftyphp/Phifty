<?php
namespace Phifty\TwigExtensions\ReactDirective;
use Twig_Node;
use Twig_Compiler;
use Twig_Node_Expression;
use Twig_Node_Expression_Array;
use Twig_Node_Expression_Constant;
use Twig_Node_Expression_Name;
use Twig_Node_Expression_GetAttr;

class ReactAppNode extends Twig_Node
{
    public function __construct(array $attributes, $lineno, $tag = null)
    {
        parent::__construct(array(), $attributes, $lineno, $tag);
    }

    protected function writeEcho($compiler, $str)
    {
        $compiler->raw('echo "' . addslashes($str) . '";' . PHP_EOL);
    }

    public function compile(Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        $appName = $this->getAttribute('reactapp');
        $configNode = $this->getAttribute('config');
        $bindTo = $this->getAttribute('bind_to');

        $compiler->raw("// ReactDirectiveExtension\n");

        if ($bindTo) {
            $compiler->raw("\$elementId = '$bindTo';\n");
        } else {
            $compiler->raw("\$elementId = uniqid('$appName');\n");
        }

        $configVarName = $compiler->getVarName();
        $compiler->raw("\$$configVarName = ");
        $configNode->compile($compiler);
        $compiler->raw(";\n");


        if ($bindTo) {
            // don't render react app container here
        } else {
            $this->writeEcho($compiler, "<div id=\"{\$elementId}\"> </div>\n");
        }
        $this->writeEcho($compiler, "<script type=\"text/javascript\">\n");
        $this->writeEcho($compiler, "jQuery(function() {\n");

        if ($compiler->getEnvironment()->isDebug()) {
            $compiler->raw("echo 'console.info(\'Initialize $appName on \$elementId:\');';\n");
            $compiler->raw("echo 'console.dir(';");
            $compiler->raw("echo json_encode(\$$configVarName, JSON_PRETTY_PRINT);\n");
            $compiler->raw("echo ');';");
        }

        $this->writeEcho($compiler, "var app = React.createElement($appName, ");

        $compiler->raw("echo json_encode(\$$configVarName, JSON_PRETTY_PRINT);\n");

        $this->writeEcho($compiler, ");\n");
       
        // React.render(app, document.getElementById('{{eid}}'));
        $this->writeEcho($compiler, "   React.render(app, document.getElementById(\"{\$elementId}\"));\n");

        $this->writeEcho($compiler, "});\n");
        $this->writeEcho($compiler, "</script>\n");
    }
}




