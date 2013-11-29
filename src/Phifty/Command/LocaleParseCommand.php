<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Symfony\Component\Finder\Finder;
use Phifty\Kernel;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Phifty\FileUtils;
use Phifty;

class LocaleParseCommand extends Command
{

    public function brief() { return 'parse and update message catalogs.'; }

    public function options($opts)
    {
        $opts->add('f|force','force');
    }

    public function execute()
    {
        $kernel = kernel();
        $localeDir = $kernel->config->get('framework','Services.LocaleService.Directory') ?: 'locale';
        $frameworkLocaleDir = PH_ROOT . DIRECTORY_SEPARATOR . $localeDir;

        if ( $langsConfig = $kernel->config->get('framework','Services.LocaleService.Langs') ) {
            $langs = $langsConfig->config;
        } else {
            $this->logger->warn("Services.LocaleService.Langs is required.");
            $this->logger->warn("Using default lang 'en' for locale");
            $langs= array('en');
        }

        $cwd = getcwd();
        $appPoFiles = array();
        $frameworkId = Kernel::FRAMEWORK_ID;
        $appId       = $kernel->config->framework->ApplicationID;

        $frameworkPoFilename = $frameworkId . '.po';
        $appPoFilename       = $appId . '.po';

        // prepare po files from framework po source files,
        // if we don't have one for the specific language.
        foreach( $langs as $langId ) {
            $poDir        = $localeDir . DIRECTORY_SEPARATOR . $langId . DIRECTORY_SEPARATOR . 'LC_MESSAGES';
            $sourcePoPath = $frameworkLocaleDir . DIRECTORY_SEPARATOR . $langId . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . $frameworkId . '.po';
            $targetPoPath = $localeDir . DIRECTORY_SEPARATOR . $langId . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . $appId . '.po';

            if ( ! file_exists($poDir) ) {
                mkdir($poDir, 0755, true);
            }

            if ( $this->options && $this->options->force || file_exists( $sourcePoPath ) && ! file_exists( $targetPoPath ) ) {
                $this->logger->info("Creating $targetPoPath");

                if ( $sourcePoPath != $targetPoPath ) {
                    copy($sourcePoPath, $targetPoPath);
                }
            }
        }

        $engine = new Phifty\View\Twig;
        $twig = $engine->getRenderer();

        $designTemplateDir = 'design/production';
        if ( file_exists($designTemplateDir) ) {
            $this->logger->info("Compiling design templates...");
            foreach (new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($designTemplateDir),
                    RecursiveIteratorIterator::LEAVES_ONLY) as $file) 
            {
                // force compilation
                if( preg_match( '/\.(html?|twig)$/', $file ) ) {
                    $this->logger->info( "Compiling " . $file->getPathname() ,1);
                    $twig->loadTemplate( substr($file, strlen($designTemplateDir) + 1) );
                }
            }
        }

        // Compile templates from bundles
        $this->logger->info("Compiling bundle templates...");
        foreach( $kernel->bundles as $bundle ) {
            $pluginDir = $bundle->locate();
            $templateDir = $bundle->getTemplateDir();
            if ( ! file_exists($templateDir) ) {
                continue;
            }
            foreach (new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($templateDir),
                    RecursiveIteratorIterator::LEAVES_ONLY) as $file) 
            {
                // force compilation
                if( preg_match( '/\.(html?|twig)$/', $file ) ) {
                    $this->logger->info( FileUtils::remove_cwd($file->getPathname()) ,1);
                    $twig->loadTemplate( substr($file, strlen(dirname($pluginDir)) + 1) );
                }
            }
        }

        $potFile = $localeDir . DIRECTORY_SEPARATOR . 'messages.pot';
        $this->logger->info("Creating pot file: $potFile");
        touch($potFile);

        $scanDirs = func_get_args(); // get paths from command-line

        if ( empty($scanDirs) ) {
            $scanDirs[] = PH_ROOT . DIRECTORY_SEPARATOR . 'src'; // phifty src
            $scanDirs[] = PH_ROOT . DIRECTORY_SEPARATOR . 'applications'; // phifty applications
            $scanDirs[] = PH_ROOT . DIRECTORY_SEPARATOR . 'bundles';
            $scanDirs[] = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'applications';
            $scanDirs[] = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'bundles';
            $scanDirs[] = $kernel->getCacheDir();
        }
        $scanDirs = array_filter( $scanDirs, 'file_exists' );
        if ( empty($scanDirs) ) {
            throw new Exception("Non of existing directories");
        }

        foreach( $scanDirs as $scanDir ) {
            $this->logger->info("Parsing from $scanDir...");

            $phpFinder = Finder::create()->files()->name('*.php')->in( $scanDir );
            $phpFiles = array();
            foreach( $phpFinder as $phpFile ) {
                $phpFiles[] = $phpFile;
            }

            if ( empty($phpFiles) ) {
                continue;
            }

            $cmd = sprintf("xgettext -j --package-name=%s -o %s --from-code=UTF-8 -n -L PHP " . join(" ",$phpFiles)
                ,kernel()->applicationID, $potFile);
            $this->logger->debug($cmd,1);
            system($cmd, $retval);
            if ( $retval != 0 ) {
                die('xgettext error');
            }
        }

        $this->logger->info("Updating message catalog...");

        // Update message catalog
        $finder = Finder::create()->files()->name('*.po')->in( $localeDir );
        foreach ( $finder->getIterator() as $file ) {
            $shortPathname = $file;

            $this->logger->info("Updating $shortPathname");
            $cmd = sprintf('msgmerge --previous --verbose --no-fuzzy-matching --update %s %s', $shortPathname, $potFile);
            $this->logger->debug($cmd);
            system($cmd, $retval);
            if ( $retval != 0 )
                die('xgettext error');

            /*
            $this->logger->info("Removing obsolete messages for $shortPathname");
            $msg = sprintf('msgattrib --output-file=%s --no-obsolete %s', $shortPathname, $potFile);
            $this->logger->debug($cmd);
            */

            $moPathname = futil_replace_extension($shortPathname,'mo');
            $this->logger->info("Compiling messages $shortPathname");
            $cmd = sprintf('msgfmt -v -o %s %s', $moPathname, $shortPathname);
            $this->logger->debug($cmd);
            system($cmd, $retval);
            if ( $retval != 0 )
                die('xgettext error');
        }
    }

}
