<?php
namespace Phifty\Web;
use Phifty\View\TemplateView;
use FormKit\Element;
use Twig_Loader_Array;
use Twig_Environment;


/*
 * TODO: twig extension:
 *
 *    {% region '/bs/crud/list', { arguments ...  } %}
 *
 */

class Region extends TemplateView
{
    public $regionId;

    public $container;
    public $path;
    public $arguments = array();
    public $options = array();

    public function __construct($path, $arguments = array(), $options = array()) 
    {
        $this->path = $path;
        $this->arguments = $arguments;
        $this->options = $options;
        $this->container = $this->createContainer();
    }

    public function createContainer()
    {
        $container = new Element('div');
        $container->addClass('__region');
        return $container;
    }

    public function getRegionId()
    {
        if ( $this->regionId ) {
            return $this->regionId;
        }
        return $this->regionId = preg_replace('#\W+#', '_', $this->path) . '-' . md5(microtime());
    }

    public function setRegionId($id)
    {
        $this->regionId = $id;
    }

    public function getTemplate()
    {
        return <<<TEMPL
{{ View.container.render()|raw }}
<script type="text/javascript">
$(document.body).ready(function() {
    $('#{{View.getRegionId()}}').asRegion().load( '{{View.path|raw}}' , {{ View.arguments|json_encode|raw }} );
});
</script>
TEMPL;

    }

    public function render($args = array())
    {
        // set the region ID to container when rendering content
        $this->container->setId( $this->getRegionId() );

        $loader = new Twig_Loader_Array(array(
            'region.html' =>  $this->getTemplate(),
        ));
        $twig = new Twig_Environment($loader);
        return $twig->render('region.html', $this->mergeTemplateArguments($args));
    }

    public function __toString() 
    {
        return $this->render();
    }


    static public function create($path, $arguments = array(), $regionId = null) 
    {
        $region = new static($path, $arguments);
        if ( $regionId ) {
            $region->setRegionId($regionId);
        }
        return $region;
    }

}




