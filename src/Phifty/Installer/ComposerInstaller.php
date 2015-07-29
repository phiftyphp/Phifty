<?php
namespace Phifty\Installer;
use Composer\Script\CommandEvent;
use ConfigKit\ConfigCompiler;


/**
 * UUID class
 *
 * The following class generates VALID RFC 4211 COMPLIANT
 * Universally Unique IDentifiers (UUID) version 3, 4 and 5.
 *
 * UUIDs generated validates using OSSP UUID Tool, and output
 * for named-based UUIDs are exactly the same. This is a pure
 * PHP implementation.
 *
 * @author Andrew Moore
 * @link http://www.php.net/manual/en/function.uniqid.php#94959
 */
class UUID
{
    /**
     * Generate v3 UUID
     *
     * Version 3 UUIDs are named based. They require a namespace (another 
     * valid UUID) and a value (the name). Given the same namespace and 
     * name, the output is always the same.
     * 
     * @param   uuid    $namespace
     * @param   string  $name
     */
    public static function v3($namespace, $name)
    {
        if(!self::is_valid($namespace)) return false;
 
        // Get hexadecimal components of namespace
        $nhex = str_replace(array('-','{','}'), '', $namespace);
 
        // Binary Value
        $nstr = '';
 
        // Convert Namespace UUID to bits
        for($i = 0; $i < strlen($nhex); $i+=2) 
        {
            $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
        }
 
        // Calculate hash value
        $hash = md5($nstr . $name);
 
        return sprintf('%08s-%04s-%04x-%04x-%12s',
 
        // 32 bits for "time_low"
        substr($hash, 0, 8),
 
        // 16 bits for "time_mid"
        substr($hash, 8, 4),
 
        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 3
        (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,
 
        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
 
        // 48 bits for "node"
        substr($hash, 20, 12)
        );
    }
 
    /**
     * 
     * Generate v4 UUID
     * 
     * Version 4 UUIDs are pseudo-random.
     */
    public static function v4() 
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

        // 32 bits for "time_low"
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),

        // 16 bits for "time_mid"
        mt_rand(0, 0xffff),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000,
 
        // 48 bits for "node"
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
 
    /**
     * Generate v5 UUID
     * 
     * Version 5 UUIDs are named based. They require a namespace (another 
     * valid UUID) and a value (the name). Given the same namespace and 
     * name, the output is always the same.
     * 
     * @param   uuid    $namespace
     * @param   string  $name
     */
    public static function v5($namespace, $name) 
    {
        if(!self::is_valid($namespace)) return false;
 
        // Get hexadecimal components of namespace
        $nhex = str_replace(array('-','{','}'), '', $namespace);
 
        // Binary Value
        $nstr = '';
 
        // Convert Namespace UUID to bits
        for($i = 0; $i < strlen($nhex); $i+=2) 
        {
            $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
        }
 
        // Calculate hash value
        $hash = sha1($nstr . $name);
 
        return sprintf('%08s-%04s-%04x-%04x-%12s',
 
        // 32 bits for "time_low"
        substr($hash, 0, 8),
 
        // 16 bits for "time_mid"
        substr($hash, 8, 4),
 
        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 5
        (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,
 
        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
 
        // 48 bits for "node"
        substr($hash, 20, 12)
        );
    }
 
    public static function is_valid($uuid) {
        return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?'.
                      '[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
    }
}

/*
    {
        "scripts": {
            "post-install-cmd": [
                "Phifty\\Installer\\ComposerInstaller::postInstall"
            ]
        }
    }
*/
class ComposerInstaller
{
    public static function postInstall(CommandEvent $event)
    {
        $composer = $event->getComposer();

        echo "Creating directory structure...\n";
        $dirs = array();
        $dirs[] = 'bin';
        $dirs[] = 'cache'.DIRECTORY_SEPARATOR.'view';
        $dirs[] = 'cache'.DIRECTORY_SEPARATOR.'config';
        $dirs[] = 'applications';
        $dirs[] = 'config';
        foreach( $dirs as $dir ) {
            futil_mkdir_if_not_exists($dir, 0755, true);
        }


        echo "Installing main.php\n";
        if (! file_exists('main.php')) {
            copy('vendor/corneltek/phifty-core/main_app.php','main.php');
        }

        if (! file_exists('config/framework.yml')) {
            echo "Installing framework config...\n";
            $appId = basename(getcwd());
            $appName = ucfirst(basename(getcwd()));
            $uuid = UUID::v4();
            $domain = $appId . '.dev';
            echo "ApplicationId: $appId, Domain: $domain, UUID: $uuid\n";
            $content = self::createConfigFromTemplate($appId, $appName, $uuid, $domain);
            file_put_contents("config/framework.yml", $content );
        }


        if (! file_exists('config/database.yml')) {
            echo "Installing database config...\n";
            copy('vendor/corneltek/phifty-core/config/database.yml', 'config/database.yml');

            echo "Rewriting database config...\n";
            $config = ConfigCompiler::load('config/database.yml');

            $config['data_sources']['default'] = array(
                'database' => basename(getcwd()),
                'driver' => 'mysql',
                'host' => 'localhost',
                'user' => 'user',
                'pass' => 'pass',
            );
            file_put_contents("config/database.yml", yaml_emit($config) );
        }



        if ( ! file_exists('locale') ) {
            echo "Installing locale...\n";
            passthru('rsync -r vendor/corneltek/phifty-core/locale/ locale/');
        }

        if ( ! file_exists('webroot') ) {
            passthru('rsync -r vendor/corneltek/phifty-core/webroot/ webroot/');
        }

        echo "Changing permissions...\n";
        $chmods = array();
        $chmods[] = array( "og+rw" , "cache" );
        $chmods[] = array( "og+rw" , "webroot" . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload');
        foreach ($chmods as $mod) {
            list($mod, $path) = $mod;
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            system("chmod -R $mod $path");
        }

        if ( ! file_exists('.gitignore') ) {
            copy('vendor/corneltek/phifty-core/.gitignore','.gitignore');
        }
        echo "Done";
    }

    public static function postPackageInstall(CommandEvent $event)
    {
        $installedPackage = $event->getOperation()->getPackage();
    }


    public static function createConfigFromTemplate($id, $name, $uuid, $domain = 'localhost') {
        $config = <<<CONFIG
---
ApplicationName: $name
ApplicationID:   $id
ApplicationUUID: $uuid
Domain: $domain
Applications:
Services:
  CurrentUserService:
    Class: Phifty\Security\CurrentUser
    Model: UserBundle\Model\User
  ActionService:
  RouterService:
  ViewService:
    Backend: twig
    Class: Phifty\View
    TemplateDirs: ~
  LibraryService:
  MailerService:
    Transport: MailTransport
  CacheService:
  SessionService:
  LocaleService:
    Directory: locale
    Default: zh_TW
    Langs:
      - en
      - zh_TW
  TwigService:
    TemplateDirs:
      - bundles
  AssetService:
  BundleService:
    Paths:
      - bundles
  KendoService:
    Rules:
      - UserBundle::AccessRules
Bundles:
CONFIG;
        return $config;
    }

}





