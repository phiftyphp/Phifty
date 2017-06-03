<?php

namespace Phifty\ServiceProvider;

use ActionKit\ActionRunner;
use ActionKit\ServiceContainer;
use ActionKit\ActionTemplate\TwigActionTemplate;
use ActionKit\ActionTemplate\CodeGenActionTemplate;
use ActionKit\ActionTemplate\RecordActionTemplate;
use ActionKit\ActionTemplate\UpdateOrderingRecordActionTemplate;
use ActionKit\ActionRequest;
use ActionKit\Action;
use Phifty\Kernel;

class ActionServiceProvider extends BaseServiceProvider
{
    public function getId()
    {
        return 'action';
    }

    public function register(Kernel $k, array $options = array())
    {
        $k->actionService = function () use ($k, $options) {
            $container = new ServiceContainer();
            $container['cache_dir'] = $k->cacheDir;
            if ($k->locale) {
                $container['locale'] = $k->locale->current;
            }

            if (isset($options['DefaultFieldView'])) {
                Action::$defaultFieldView = $options['DefaultFieldView'];
            }

            $generator = $container['generator'];
            $generator->registerTemplate('TwigActionTemplate', new TwigActionTemplate());
            $generator->registerTemplate('CodeGenActionTemplate', new CodeGenActionTemplate());
            $generator->registerTemplate('RecordActionTemplate', new RecordActionTemplate());
            $generator->registerTemplate('UpdateOrderingRecordActionTemplate', new UpdateOrderingRecordActionTemplate());

            return $container;
        };

        $k->actionRunner = function () use ($k) {
            $actionRunner = new ActionRunner($k->actionService);
            $actionRunner->registerAutoloader();
            // $actionRunner->setDebug();
            return $actionRunner;
        };

        $k->action = function () use ($k) {
            return $k->actionRunner;
        };
    }

    public function boot(Kernel $k)
    {
        $k->event->register('view.init', function ($view) use ($k) {
            $view['Action'] = $k->actionRunner;
        });

        $k->event->register('request.before', function () use ($k) {
            if (!ActionRequest::hasAction($_REQUEST)) {
                return;
            }

            $runner = $k->actionRunner;
            // the new trigger for actions defined in Bundle::actions method
            $k->event->trigger('phifty.prepare_actions');
            $k->event->trigger('phifty.before_action');
            $strout = fopen('php://output', 'w');

            // If we found any ajax action, exit the application
            if ($runner->handleWith($strout, $_REQUEST, $_FILES)) {
                exit(0);
            }
        });
    }
}
