<?php
namespace Phifty\View;
use ReflectionObject;
use Exception;
use Twig_Loader_Filesystem;
use Twig_Loader_String;
use Twig_Environment;


/**
 * TODO: Refactor this out to a package.
 */
abstract class TemplateView
{
    private $_classDir;

    public function getClassDir()
    {
        if ( $this->_classDir ) {
            return $this->_classDir;
        }
        $ref = new ReflectionObject($this);
        return $this->_classDir = dirname($ref->getFilename());
    }

    public function getTemplateDir()
    {
        return $this->getClassDir() . DIRECTORY_SEPARATOR . 'Templates';
    }


    public function createTwigStringLoader() 
    {
        return new Twig_Loader_String();
    }


    public function createTwigFileSystemLoader()
    {
        $dir = $this->getTemplateDir();
        if ( ! file_exists($dir) ) {
            throw RuntimeException("Directory $dir for TemplateView does not exist.");
        }
        return new Twig_Loader_Filesystem($dir);
    }

    public function createTwigEnvironment($loader)
    {
        return new Twig_Environment($loader, $this->getTwigConfig());
    }

    public function getTwigConfig()
    {
        return array();
    }

    public function getDefaultArguments()
    {
        return array('View' => $this );
    }


    public function mergeTemplateArguments($args = array())
    {
        return array_merge( $this->getDefaultArguments() , $args);
    }


    /**
     * When using renderTemplateFile method, we are creating the twig filesystem loader for use.
     *
     * @param string $templateFile template filename.
     * @param array $arguments arguments for rendering.
     */
    /* $twig->render('index.html', array('the' => 'variables', 'go' => 'here')); */
    public function renderTemplateFile($templateFile,$arguments = array())
    {
        $loader = $this->createTwigFileSystemLoader();
        $twig = $this->createTwigEnvironment($loader);
        return $twig->render($templateFile,  $this->mergeTemplateArguments($arguments) );
    }

    public function renderTemplateString($template, $arguments = array())
    {
        $loader = $this->createTwigStringLoader();
        $twig = $this->createTwigEnvironment($loader);
        return $twig->render($template, $this->mergeTemplateArguments($arguments) );
    }

}

