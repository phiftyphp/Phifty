<?php
namespace Phifty\Console\Command;
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
        $opts->add('fastcgi:=string', 'fastcgi socket');
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
        } else {
            $serverName = $kernel->config->get('framework', 'Domain');
            $serverAliases = $kernel->config->get('framework', 'DomainAliases') ?: [];
            $documentRoot = $kernel->webroot;


            $etc = dirname(php_ini_loaded_file());
            $fpmConfigFile = $etc . DIRECTORY_SEPARATOR . 'php-fpm.conf';
            if (file_exists($fpmConfigFile)) {
                $fpmConfig = parse_ini_file($fpmConfigFile);
            } else {
                $fpmConfig = [];
            }

            $listen = $this->options->{'fastcgi'}
                ?: (isset($fpmConfig['listen'])
                    ? $fpmConfig['listen']
                    : 'localhost:9000');

            echo <<<OUT
# vim:et:sw=2:ts=2:sts=2:
server {
  listen 80;
  server_name $serverName;
  index index.html index.htm index.php;
  server_name_in_redirect off;
  root $documentRoot;
  autoindex off;
  location / {
    if (!-e \$request_filename) {
      rewrite ^(.*)$ /index.php$1 last;
    }
  }
  location ~ \.php {
    fastcgi_split_path_info ^(.+\.php)(/.*)$;
    fastcgi_param  SCRIPT_FILENAME    \$document_root\$fastcgi_script_name;
    fastcgi_param  SCRIPT_NAME        \$fastcgi_script_name;
    fastcgi_param  PATH_INFO          \$fastcgi_path_info;
    fastcgi_index  index.php;
    fastcgi_pass   $listen;
    include fastcgi_params;
  }
}
OUT;
        }
    }
}



