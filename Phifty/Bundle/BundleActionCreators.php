<?php

namespace Phifty\Bundle;

trait BundleActionCreators
{
    /**
     * @var Phifty\Generator\AppActionGenerator
     */
    protected $_actionGenerator;

    protected function getActionGenerator()
    {
        if ($this->_actionGenerator) {
            return $this->_actionGenerator;
        }
        return $this->_actionGenerator = new AppActionGenerator($this->kernel, $this);
    }

    public function addCRUDAction($modelName, $types = array())
    {
        @trigger_error('addCRUDAction will be deprecated, please use addRecordAction instead', E_USER_DEPRECATED);
        return $this->addRecordAction($modelName, $types);
    }

    /**
     *
     *
     * This method provides an API to register the user defined action class that can
     * be generated in the runtime.
     *
     * phifty.before_action will be triggered when users send the action request.
     *
     * @param string $modelName model class
     * @param array  $types action types (Create, Update, Delete, BulkCopy, BulkDelete.....)
     */
    public function addRecordAction($modelName, $types = null)
    {
        $self = $this;
        $this->kernel->event->register('phifty.before_action', function () use ($self, $types, $modelName) {
            $generator = $self->getActionGenerator();
            $generator->addRecordAction($modelName, $types);
        });
    }


    /**
     * Register import action for an record.
     *
     * @param string $modelName the model name Org
     */
    public function addImportAction($modelName)
    {
        $self = $this;
        $this->kernel->event->register('phifty.before_action', function () use ($self, $modelName) {
            $generator = $self->getActionGenerator();
            $generator->addSimpleImportAction($modelName);
        });
    }

    /**
     * Register/Generate update ordering action
     *
     * @param string $modelName model class
     */
    public function addUpdateOrderingAction($modelName)
    {
        $self = $this;
        $this->kernel->event->register('phifty.before_action', function () use ($self, $modelName) {
            $generator = $self->getActionGenerator();
            $generator->addUpdateOrderingAction($modelName);
        });
    }
}
