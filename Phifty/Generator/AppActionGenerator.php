<?php

namespace Phifty\Generator;

use Phifty\Kernel;
use Phifty\Bundle;

class AppActionGenerator
{
    protected $kernel;

    protected $bundle;

    /**
     *  The default action types is used in CRUD action generator.
     */
    public $defaultActionTypes = array(
        ['prefix' => 'Create'],
        ['prefix' => 'Update'],
        ['prefix' => 'Delete'],
        ['prefix' => 'BulkDelete'],
    );

    public function __construct(Kernel $kernel, Bundle $bundle)
    {
        $this->kernel = $kernel;
        $this->bundle = $bundle;
    }

    /**
     * Register/Generate update ordering action
     *
     * @param string $modelName model class
     */
    public function addUpdateOrderingAction($modelName)
    {
        $this->kernel->actionLoader->registerTemplateAction('SortRecordActionTemplate', array(
            'namespace' => $this->bundle->getNamespace(),
            'model' => $modelName,
        ));
    }


    /**
     * Register/Generate CRUD actions
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
    public function addRecordAction($modelName, $types = array())
    {
        if (!$types || empty($types)) {
            $types = $this->defaultActionTypes;
        }
        $this->kernel->actionLoader->registerTemplateAction('RecordActionTemplate', array(
            'namespace' => $this->bundle->getNamespace(),
            'model' => $modelName,
            'types' => (array) $types,
        ));
    }


    /**
     * Register import action for an record.
     *
     * @param string $modelName the model name Org
     */
    public function addSimpleImportAction($modelName)
    {
        $className = $this->bundle->getNamespace() . '\\Action\\Import' . $modelName . 'Simple';
        $recordClass = $this->bundle->getNamespace() . '\\Model\\' . $modelName;
        $this->kernel->actionLoader->registerTemplateAction('CodeGenActionTemplate', [
            "action_class" => $className,
            "extends"      => "\\CRUD\\Action\\ImportSimple",
            "properties"   => [ "recordClass" => $recordClass, ],
        ]);
    }
}
