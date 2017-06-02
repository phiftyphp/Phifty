<?php

namespace Phifty\ServiceProvider;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Handler\StreamHandler;
use Pimple\Container;
use Phifty\Kernel;

class MonologServiceProvider extends BaseServiceProvider
{
    public function getId()
    {
        return 'monolog';
    }

    public function register(Kernel $kernel, array $options = array())
    {
        $kernel->monolog = function () use ($kernel, $options) {
            // create sub container for monolog
            $container = new Container();

            $container['logger'] = $container->factory(function () {
                $channel = isset($options['Channel']) ? $options['Channel'] : 'phifty';
                $logger = new Logger($channel);
                // $log->pushProcessor(new Monolog\Processor\IntrospectionProcessor(Monolog\Logger::INFO))
                return $logger;
            });

            // Create different logger
            $container['file'] = function ($c) {
                $logger = $c['logger'];
                /*
                // This is the Handler, I choose a RotatingFileHandler in ordert to have
                // a logfile per day, for the last 60 days. Inside the logs I wanted to
                // have any message from the “info” level above.
                $logger->pushHandler(new Monolog\Handler\RotatingFileHandler(“logs/events.log”,60, Monolog\Logger::INFO));
                */
                $logFilePath = implode(DIRECTORY_SEPARATOR, ['logs', date('c').'.log']);
                $logger->pushHandler(new StreamHandler($logFilePath, Logger::ERROR));
            };

            if (isset($options['SwiftMailerHandler'])) {
                $container['mail'] = function ($c) use ($kernel, $logger, $options) {
                    $from = $options['SwiftMailerHandler']['From'];
                    $to = $options['SwiftMailerHandler']['To'];

                    // Get transport
                    $mailer = $kernel->mailer;

                    // Create an empty message
                    /*
                    $message = Swift_Message::newInstance('Something wrong occurred!')
                        ->setFrom(['yoanlin93@gmail.com' => 'Error Reporting service'])
                        ->setTo(['enya0625@gmail.com' => 'Myself']);
                     */
                    $message = Swift_Message::newInstance('Something wrong occurred!')
                        ->setFrom($from)
                        ->setTo($to);

                    $message->setBody('', 'text/html');
                    $handler = new SwiftMailerHandler($mailer, $message, Logger::WARNING);

                    // The new SwiftMailerHandler: it takes the $mailer and the $message
                    // as the first two arguments, and in the third argument we specify
                    // that we will use this handler (= we will receive e-mails)
                    // only for warnings and above

                    // Apply the html formatter to the handler
                    $htmlFormatter = new HtmlFormatter();
                    $handler->setFormatter($htmlFormatter);

                    //Register the SwiftMailerHandler with the logger
                    $c['logger']->pushHandler($handler);
                };
            }

            // define console logger
            $container['console'] = function ($c) {
                // line formatter
                $formatter = new LineFormatter("[%datetime%] %channel%.%level_name%: %message%\n");
                $handler = new StreamHandler('php://stderr', Logger::ERROR);
                $handler->setFormatter($formatter);
                $logger = $c['logger'];
                $logger->pushHandler($handler);

                return $logger;
            };

            return $container;
        };
    }
}
