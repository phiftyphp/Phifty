<?php
namespace Phifty\Command;
use CLIFramework\Command;
use CodeGen\Frameworks\Apache2\VirtualHost;
use CodeGen\Frameworks\Apache2\Directory;
use Phifty\Kernel;

class ServerConfigCommand extends Command
{

    public function options($opts)
    {
        $opts->add('apache2');
        $opts->add('nginx');
    }

    protected function generateApache2Config(Kernel $kernel)
    {
        $appId = $kernel->applicationID;
        $serverName = $kernel->config->get('framework', 'Domain');
        $serverAliases = $kernel->config->get('framework', 'DomainAliases') ?: [];
        $config = new VirtualHost('*', 80);
        $config->setServerName($serverName);
        $config->setServerAliases($serverAliases);
        $config->setDocumentRoot($kernel->webroot);
        if ($appId) {
            $config->setCustomLog("\${APACHE_LOG_DIR}/{$appId}-access.log combined");
        }
        $config->setRewriteEngine('on');
        $config->addRewriteCond('%{DOCUMENT_ROOT}%{REQUEST_FILENAME}', '!-f');
        $config->addRewriteCond('%{DOCUMENT_ROOT}%{REQUEST_FILENAME}', '!-s');
        $config->addRewriteCond('%{DOCUMENT_ROOT}%{REQUEST_FILENAME}', '!-d');
        $config->addRewriteRule('^(.*)$', '%{DOCUMENT_ROOT}/index.php/$1', '[NC,L]');

        $dir = new Directory($kernel->webroot);
        $dir->addOption('-Indexes');
        $dir->addOption('+FollowSymLinks');
        $dir->setAllowOverride('None');
        $dir->setRequire('all granted');
        $config->addDirective($dir);
        return $config;
    }

    public function execute()
    {

        $kernel = kernel();
        if ($this->options->apache2) {
            $config = $this->generateApache2Config($kernel);
            echo $config->generate();
        }
    }
}



