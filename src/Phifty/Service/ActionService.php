<?php
namespace Phifty\Service;
use Exception;
use ActionKit\ActionRunner;
use ActionKit\ServiceContainer;
use ActionKit\ActionTemplate\FileBasedActionTemplate;
use ActionKit\ActionTemplate\CodeGenActionTemplate;
use ActionKit\ActionTemplate\RecordActionTemplate;
use ActionKit\ActionTemplate\UpdateOrderingRecordActionTemplate;

class ActionService
    implements ServiceRegister
{
    public function getId() { return 'action'; }

    public function register($kernel, $options = array() )
    {
        $container = new ServiceContainer;
        $generator = $container['generator'];
        $generator->registerTemplate('FileBasedActionTemplate', new FileBasedActionTemplate);
        $generator->registerTemplate('CodeGenActionTemplate', new CodeGenActionTemplate);
        $generator->registerTemplate('RecordActionTemplate', new RecordActionTemplate);
        $generator->registerTemplate('UpdateOrderingRecordActionTemplate', new UpdateOrderingRecordActionTemplate);

        $action = new ActionRunner($container);
        $action->registerAutoloader();

        $kernel->action = function() use ($options,$action) {
            return $action;
        };

        $kernel->event->register('view.init', function($view) use ($action) {
            $view->args['Action'] = $action;
        });

        $kernel->event->register('phifty.before_path_dispatch',function() use ($kernel) {
            // check if there is $_POST['action'] or $_GET['action']
            if ( ! isset($_REQUEST['action']) ) {
                return;
            }

            $runner = $kernel->action;  // get runner
            $kernel->event->trigger('phifty.before_action');
            $result = $runner->handleWith(STDOUT, $_REQUEST);

        });
    }
}
