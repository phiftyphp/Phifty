<?php
namespace Phifty\Model;
use LazyRecord\Schema\SchemaDeclare\Column as DeclareColumn;

class Column extends DeclareColumn
{
    public $widgetClass;

    public $widgetAttributes = array();

    public function renderAs( $type , $widgetAttributes = array() )
    {
        $this->widgetClass = $type;
        $this->widgetAttributes = $widgetAttributes;
        return $this;
    }
}
