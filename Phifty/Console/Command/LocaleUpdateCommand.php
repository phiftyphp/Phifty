<?php
namespace Phifty\Console\Command;

use CLIFramework\Command;
use Symfony\Component\Finder\Finder;
use Phifty\Kernel;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Phifty\FileUtils;
use Phifty;

class LocaleUpdateCommand extends Command
{
    public function brief()
    {
        return 'compile message files';
    }

    public function options($opts)
    {
        $opts->add('f|force', 'force');
    }

    public function execute()
    {
        $kernel             = kernel();
        $localeDir          = $kernel->config->get('framework', 'Services.LocaleService.Directory') ?: 'locale';
        $frameworkLocaleDir = PH_ROOT . DIRECTORY_SEPARATOR . 'locale';
        $langs              = $kernel->config->get('framework', 'Services.LocaleService.Langs')->config;

        $cwd = getcwd();
        $appPoFiles = array();
        $frameworkId = Kernel::FRAMEWORK_ID;
        $appId       = $kernel->config->framework->ApplicationID;

        $frameworkPoFilename = $frameworkId . '.po';
        $appPoFilename       = $appId . '.po';

        $this->logger->info("Compiling message catalog...");

        // Update message catalog
        $finder = Finder::create()->files()->name('*.po')->in($localeDir);
        foreach ($finder->getIterator() as $file) {
            $targetFile = futil_replace_extension($file, 'mo');

            $this->logger->info("Compiling messages $file to $targetFile");
            $cmd = sprintf('msgfmt -v --use-fuzzy -o %s %s', $targetFile, $file);
            $this->logger->debug($cmd);
            system($cmd, $retval);
            if ($retval != 0) {
                die('xgettext error');
            }
        }
    }
}
