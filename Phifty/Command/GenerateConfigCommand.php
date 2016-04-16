<?php
namespace Phifty\Command;
use CLIFramework\Command;
use CodeGen\Frameworks\Apache2\VirtualHostConfig;
use Phifty\Kernel;

/*
<VirtualHost *:80>
  ServerName bossnet-test.lifeplus.tw
  ServerAlias  bossnet-test.lifeplus.tw bossnet-test.corneltek.com

  ServerAdmin webmaster@localhost
  DocumentRoot /var/www/portal/webroot
  ErrorLog ${APACHE_LOG_DIR}/lifeplus-error.log
  CustomLog ${APACHE_LOG_DIR}/lifeplus-access.log combined
</VirtualHost>
 */


class GenerateConfigCommand extends Command
{

    public function options($opts)
    {
        $opts->add('apache2');
        $opts->add('nginx');
    }



    /**
    * TODO: support Directory directive
    <Directory ....>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
    </Directory>
    */
    protected function generateApache2Config(Kernel $kernel)
    {
        $appId = $kernel->applicationID;
        $serverName = $kernel->config->get('framework', 'Domain');
        $serverAliases = $kernel->config->get('framework', 'DomainAliases') ?: [];
        $config = new VirtualHostConfig('*', 80);
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



