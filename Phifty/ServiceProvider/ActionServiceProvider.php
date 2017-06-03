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

    public function register(Kernel $kernel, array $options = array())
    {
        $kernel->actionService = function () use ($kernel, $options) {
            $container = new ServiceContainer();
            $container['cache_dir'] = $kernel->cacheDir;
            if ($kernel->locale) {
                $container['locale'] = $kernel->locale->current;
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

        $kernel->actionRunner = function () use ($kernel) {
            $actionRunner = new ActionRunner($kernel->actionService);
            $actionRunner->registerAutoloader();
            // $actionRunner->setDebug();
            return $actionRunner;
        };

        $kernel->action = function () use ($kernel) {
            return $kernel->actionRunner;
        };
    }

    public function boot(Kernel $k)
    {
        $kernel->event->register('view.init', function ($view) use ($kernel) {
            $view['Action'] = $kernel->actionRunner;
        });

        $kernel->event->register('request.before', function () use ($kernel) {
            if (!ActionRequest::hasAction($_REQUEST)) {
                return;
            }

            $runner = $kernel->actionRunner;
            // the new trigger for actions defined in Bundle::actions method
            $kernel->event->trigger('phifty.prepare_actions');
            $kernel->event->trigger('phifty.before_action');
            $strout = fopen('php://output', 'w');

            // If we found any ajax action, exit the application
            if ($runner->handleWith($strout, $_REQUEST, $_FILES)) {
                exit(0);
            }
        });
    }
}
