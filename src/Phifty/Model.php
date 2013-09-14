<?php
namespace Phifty;
use LazyRecord\BaseModel;
use ActionKit\RecordAction\BaseRecordAction;

class Model extends BaseModel
{
    public function getLabel()
    {
        $label = parent::getLabel();
        return $label ? _($label) : $label;
    }

    public function getCurrentUser()
    {
        return kernel()->currentUser;
    }

    public function asCreateAction($args = array())
    {
        return $this->_newAction('Create',$args);
    }

    public function asUpdateAction($args = array())
    {
        return $this->_newAction('Update',$args);
    }

    public function asDeleteAction($args = array())
    {
        return $this->_newAction('Delete',$args);
    }

    /**
     * Create an action from existing record object
     *
     * @param string $type 'create','update','delete'
     *
     * TODO: Move to ActionKit
     */
    private function _newAction($type, $args = array() )
    {
        $class = get_class($this);
        $actionClass = BaseRecordAction::createCRUDClass($class,$type);
        return new $actionClass( $args , $this );
    }

    public function getRecordActionClass($type)
    {
        $class = get_class($this);
        return BaseRecordAction::createCRUDClass($class, $type);
    }
}
