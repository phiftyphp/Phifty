<?php

namespace Phifty\ServiceProvider;

use WebAction\ActionRunner;
use WebAction\DefaultConfigurations;
use WebAction\ActionTemplate\TwigActionTemplate;
use WebAction\ActionTemplate\CodeGenActionTemplate;
use WebAction\ActionTemplate\RecordActionTemplate;
use WebAction\ActionTemplate\UpdateOrderingRecordActionTemplate;
use WebAction\ActionRequest;
use WebAction\Action;
use Phifty\Kernel;

class ActionServiceProvider extends ServiceProvider
{
    public function getId()
    {
        return 'action';
    }

    public function register(Kernel $k, array $options = array())
    {
        $k->actionService = function () use ($k, $options) {
            $conf = new DefaultConfigurations();
            $conf['cache_dir'] = $k->cacheDir;
            if ($k->locale) {
                $conf['locale'] = $k->locale->current;
            }

            if (isset($options['DefaultFieldView'])) {
                Action::$defaultFieldView = $options['DefaultFieldView'];
            }

            $generator = $conf['generator'];
            $generator->registerTemplate('TwigActionTemplate', new TwigActionTemplate());
            $generator->registerTemplate('CodeGenActionTemplate', new CodeGenActionTemplate());
            $generator->registerTemplate('RecordActionTemplate', new RecordActionTemplate());
            $generator->registerTemplate('UpdateOrderingRecordActionTemplate', new UpdateOrderingRecordActionTemplate());

            return $conf;
        };

        $k->actionLoader = function () use ($k) {
            $loader = $k->actionService['loader'];
            $loader->autoload();
            return $loader;
        };

        $k->actionRunner = function () use ($k) {
            $actionRunner = new ActionRunner($k->actionLoader);
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

        $k->event->register('request.start', function () use ($k) {
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
