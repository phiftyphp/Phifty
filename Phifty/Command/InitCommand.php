<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Phifty\FileUtils;

function copy_if_not_exists($source,$dest)
{
    if ( ! file_exists($dest) ) {
        copy($source,$dest);
    }
}

class InitCommand extends Command
{
    public function brief()
    {
        return 'Initialize phifty project files, directories and permissions.';
    }


    public function options($opts)
    {
        $opts->add('system', 'use system commands');
    }

    public function execute()
    {
        $kernel = kernel();
        $dirs = array();
        $dirs[] = FileUtils::path_join( PH_APP_ROOT, 'cache', 'view' );
        $dirs[] = FileUtils::path_join( PH_APP_ROOT, 'cache', 'config' );
        $dirs[] = 'locale';
        $dirs[] = 'applications';
        $dirs[] = 'bin';
        $dirs[] = 'bundles';
        $dirs[] = 'config';
        $dirs[] = 'webroot';

        /* for hard links */
        $dirs[] = 'webroot' . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'images';
        $dirs[] = 'webroot' . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'css';
        $dirs[] = 'webroot' . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'js';
        $dirs[] = 'webroot' . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload';
        $dirs[] = 'webroot' . DIRECTORY_SEPARATOR . 'upload';
        FileUtils::mkpath($dirs,true);

        // TODO: create .htaccess file

        $this->logger->info( "Changing permissions..." );
        $chmods = [];
        $chmods[] = ["0777", "cache"];
        $chmods[] = ["0777", "cache/view"];
        $chmods[] = ["0777", "cache/config"];
        $chmods[] = ["0777", $kernel->webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload' ];
        $chmods[] = ["0777", $kernel->webroot . DIRECTORY_SEPARATOR . 'upload' ];
        foreach ($chmods as $mod) {
            $this->logger->info("Changing mode to {$mod[0]} on {$mod[1]}", 1 );
            if ($this->options->system) {
                system("chmod -R {$mod[0]} {$mod[1]}");
            } else {
                chmod($mod[1], octdec($mod[0]));
            }
        }
        $this->logger->info("Creating link of bin/phifty");
        if (! file_exists('bin/phifty')) {
            symlink('../vendor/bin/phifty', 'bin/phifty');
        }
    }
}
