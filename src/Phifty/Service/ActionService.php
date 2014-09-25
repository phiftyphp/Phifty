<?php
namespace Phifty\Service;
use Exception;
use ActionKit\ActionRunner;

class ActionService
    implements ServiceRegister
{
    public function getId() { return 'action'; }

    public function register($kernel, $options = array() )
    {
        $action = ActionRunner::getInstance();
        $action->registerAutoloader();

        $kernel->action = function() use ($options,$action) {
            return $action;
        };

        $kernel->event->register('view.init', function($view) {
            $view->args['Action'] = ActionRunner::getInstance();
        });

        $kernel->event->register('phifty.before_path_dispatch',function() use ($kernel) {
            // check if there is $_POST['action'] or $_GET['action']
            if ( ! isset($_REQUEST['action']) ) {
                return;
            }

            try {
                $kernel->event->trigger('phifty.before_action');

                $runner = $kernel->action; // get runner
                $result = $runner->run( $_REQUEST['action'] );
                if ( $result && $runner->isAjax() ) {
                    // Deprecated:
                    // The text/plain seems work for IE8 (IE8 wraps the 
                    // content with a '<pre>' tag.
                    header('Cache-Control: no-cache');
                    header('Content-Type: text/plain; Charset=utf-8');

                    // Since we are using "textContent" instead of "innerHTML" attributes
                    // we should output the correct json mime type.
                    // header('Content-Type: application/json; Charset=utf-8');
                    echo $result->__toString();
                    exit(0);
                }
            } catch ( Exception $e ) {
                /**
                    * Return 403 status forbidden
                    */
                header('HTTP/1.0 403');
                if ( $runner->isAjax() ) {
                    die(json_encode(array(
                            'error' => 1,
                            'message' => $e->getMessage(),
                            'line' => $e->getLine(),
                            'file' => $e->getFile(),
                    )));
                } else {
                    die( $e->__toString() );
                }
            }
        });
    }
}
