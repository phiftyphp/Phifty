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

class Region 
{

    /**
     * @var string the region element id used for javascript
     */
    public $regionId;


    /**
     * @var Element container element
     */
    public $container;

    /**
     * @var string the region path
     */
    public $path;

    /**
     * @var array region arguments
     */
    public $arguments = array();


    /**
     * @var array region options, currently not used.
     */
    public $options = array();

    static $serialId = 1;

    public function __construct($path, array $arguments = array(), array $options = array())
    {
        $this->path = $path;
        $this->arguments = $arguments;
        $this->options = $options;
        $this->container = $this->createContainerElement();
    }

    public function createContainerElement()
    {
        $el = new Element('div');
        $el->addClass('__region');
        return $el;
    }

    public function getRegionId()
    {
        if ($this->regionId) {
            return $this->regionId;
        }
        return $this->regionId = self::newRegionSerialId($this->path);
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getArguments()
    {
        return $this->arguments;
    }


    /**
     * @return FormKit\Element
     */
    public function getContainer()
    {
        return $this->container;
    }


    /**
     * Region serial ID factory method
     *
     * @param string $prefix 
     * @return string region serial ID
     */
    public static function newRegionSerialId($prefix = null)
    {
        if ($prefix) {
            return preg_replace('#\W+#', '_', $prefix) . '_' . md5(microtime()) . ++self::$serialId;
        }
        return md5(microtime()) . ++self::$serialId;
    }



    public function setRegionId($id)
    {
        $this->regionId = $id;
    }

    public function getTemplate()
    {
        return <<<TEMPL
{{Region.container.render()|raw}}
<script type="text/javascript">
$(document.body).ready(function() {
    $('#{{Region.getRegionId()}}').asRegion().load('{{Region.path|raw}}' , {{Region.arguments|json_encode|raw}});
});
</script>
TEMPL;

    }

    public function render(array $args = array())
    {
        // set the region ID to container when rendering content
        $this->container->setId( $this->getRegionId() );

        $loader = new Twig_Loader_Array(array(
            'region.html' =>  $this->getTemplate(),
        ));
        $twig = new Twig_Environment($loader);
        return $twig->render('region.html', array_merge([ 
            'Region' => $this,
        ], $args));
    }

    public function __toString() 
    {
        return $this->render();
    }

    static public function create($path, array $arguments = array(), $regionId = null) 
    {
        $region = new static($path, $arguments);
        if ( $regionId ) {
            $region->setRegionId($regionId);
        }
        return $region;
    }

}




