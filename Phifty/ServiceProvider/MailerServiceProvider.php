<?php

namespace Phifty\ServiceProvider;

use Swift_Mailer;
use ConfigKit\Accessor;
use Phifty\ComposerConfigBridge;
use Phifty\Kernel;

class MailerServiceProvider extends BaseServiceProvider implements ComposerConfigBridge
{
    public function getId()
    {
        return 'Mailer';
    }

    /**
     
     */
    public function register(Kernel $kernel, $options = array())
    {
        $kernel->mailer = function () use ($kernel, $options) {
            $accessor = new Accessor($options);
            $transportType = $accessor->Transport ?: 'MailTransport';
            $transportClass = 'Swift_'.$transportType;
            $transport = null;

            switch ($transportType) {

                case 'MailTransport':
                    $transport = $transportClass::newInstance();
                break;

                case 'SendmailTransport':
                    // sendmail transport has defined a built-in default command.
                    if ($command = $accessor->Command) {
                        $transport = $transportClass::newInstance($command);
                    } else {
                        $transport = $transportClass::newInstance();
                    }
                break;

                case 'SmtpTransport':
                    $host = $accessor->Host ?: 'localhost';
                    $port = $accessor->Port ?: 25;
                    $transport = $transportClass::newInstance($host, $port, $accessor->Encryption);
                    $transport->setUsername($accessor->Username);
                    $transport->setPassword($accessor->Password);
                    if ($mode = $accessor->AuthMode) {
                        $transport->setAuthMode($mode);
                    }
                break;

                default:
                    throw new Exception("Unsupported transport type: $transportType");
            }

            // Create the Mailer using your created Transport
            // return Swift_Mailer::newInstance($transport);
            $mailer = Swift_Mailer::newInstance($transport); // $mailer

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

    public function getComposerDependency()
    {
        return ['swiftmailer/swiftmailer' => '@stable'];
    }
}
