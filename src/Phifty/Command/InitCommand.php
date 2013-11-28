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
        FileUtils::mkpath($dirs,true);

// TODO: create .htaccess file

        $this->logger->info( "Changing permissions..." );
        $chmods = array();
        $chmods[] = array( "og+rw" , "cache" );

        $chmods[] = array( "og+rw" , $kernel->webroot . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload' );
        foreach ($chmods as $mod) {
            $this->logger->info( "{$mod[0]} {$mod[1]}", 1 );
            system("chmod -R {$mod[0]} {$mod[1]}");
        }

        $this->logger->info("Linking bin/phifty");
        if ( ! file_exists('bin/phifty') ) {
            symlink(  '../phifty/bin/phifty', 'bin/phifty' );
        }

        # init config
        $this->logger->info("Copying config files...");
        copy_if_not_exists(FileUtils::path_join(PH_ROOT,'config','framework.app.yml'), FileUtils::path_join(PH_APP_ROOT,'config','framework.yml') );
        // copy_if_not_exists(FileUtils::path_join(PH_ROOT,'config','application.dev.yml'), FileUtils::path_join(PH_APP_ROOT,'config','application.yml') );
        copy_if_not_exists(FileUtils::path_join(PH_ROOT,'config','database.app.yml'), FileUtils::path_join(PH_APP_ROOT,'config','database.yml') );
        copy_if_not_exists(FileUtils::path_join(PH_ROOT,'webroot','index.php'), FileUtils::path_join(PH_APP_ROOT,'webroot','index.php') );
        copy_if_not_exists(FileUtils::path_join(PH_ROOT,'webroot','.htaccess'), FileUtils::path_join(PH_APP_ROOT,'webroot','.htaccess') );

        $this->logger->info('Application is initialized, please edit your config files and run:');

        echo <<<DOC

    $ bin/phifty build-conf
    $ bin/phifty asset

    $ lazy build-conf config/database.yml

DOC;
    }
}
