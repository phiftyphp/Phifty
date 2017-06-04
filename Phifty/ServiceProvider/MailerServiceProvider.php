<?php

namespace Phifty\ServiceProvider;

use Phifty\ComposerConfigBridge;
use Phifty\Kernel;
use ConfigKit\Accessor;
use Swift_Mailer;
use Swift_MailTransport;
use Swift_SendmailTransport;
use Swift_SmtpTransport;
use Swift_Plugins_AntiFloodPlugin;
use Exception;

class MailerServiceProvider extends ServiceProvider implements ComposerConfigBridge
{
    public function getId()
    {
        return 'Mailer';
    }

    public static function canonicalizeConfig(Kernel $kernel, array $options)
    {
        if (isset($options['SmtpTransport'])) {
            $transportType = 'SmtpTransport';
        }

        $accessor = new Accessor($options);

        // build TransportClass from Transport key
        if (isset($options['Transport'])) {
            $transportType = $options['Transport'];
            unset($options['Transport']);
            $options['TransportClass'] = 'Swift_'.$transportType;
            if (!class_exists($cls = $options['TransportClass'], true)) {
                throw new Exception("$cls doesn't exist.");
            }
            $options[$transportType] = $options;
        }

        switch ($transportType) {
            case "SendmailTransport":
                $options[$transportType] = array_merge([
                    'Command' => 'sendmail -bs'
                ], $options[$transportType]);
                break;
            case "SmtpTransport":
                $options[$transportType] = array_merge([
                    'Host' => 'localhost',
                    'Port' => 25,
                    'Encryption' => null,
                ], $options[$transportType]);
                break;
        }
        return $options;
    }

    public function createTransport(array $options)
    {
        if (isset($options['SmtpTransport'])) {
            $transportOptions = $options['SmtpTransport'];
            $transport = Swift_SmtpTransport::newInstance(
                $transportOptions['Host'],
                $transportOptions['Port'],
                $transportOptions['Encryption']
            );
            if (isset($transportOptions['Username'])) {
                $transport->setUsername($transportOptions['Username']);
            }
            if (isset($transportOptions['Password'])) {
                $transport->setPassword($transportOptions['Password']);
            }
            if (isset($transportOptions['AuthMode'])) {
                $transport->setAuthMode($transportOptions['AuthMode']);
            }
            return $transport;
        } elseif (isset($options['MailTransport'])) {
            return Swift_MailTransport::newInstance();
        } elseif (isset($options['SendmailTransport'])) {
            $transportOptions = $options['SendmailTransport'];
            return Swift_SendmailTransport::newInstance($transportOptions['Command']);
        }
        return Swift_MailTransport::newInstance();
    }

    public function register(Kernel $kernel, array $options = array())
    {
        $self = $this;
        $kernel->mailer = function () use ($kernel, $options, $self) {
            $transport = $self->createTransport($options);

            // $container = new Container;
            // $container['transport'] = function() {  };

            // Create the Mailer using your created Transport
            // return Swift_Mailer::newInstance($transport);
            $mailer = Swift_Mailer::newInstance($transport); // $mailer
            $accessor = new Accessor($options);
            if ($accessor->Plugins) {
                foreach ($accessor->Plugins as $pluginName => $options) {
                    $pluginOptions = new Accessor($options);
                    $class = 'Swift_Plugins_'.$pluginName;
                    switch ($pluginName) {
                        case 'AntiFloodPlugin':
                            $emailLimit = $pluginOptions->EmailLimit ?: 100; // default email limit
                            $pauseSeconds = $pluginOptions->PauseSeconds ?: null;
                            $plugin = new \Swift_Plugins_AntiFloodPlugin($emailLimit, $pauseSeconds);
                            break;
                    }
                    $mailer->registerPlugin($plugin);
                }
            }
            return $mailer;
        };
    }

    public function getComposerRequire()
    {
        return ['swiftmailer/swiftmailer' => '@stable'];
    }
}
