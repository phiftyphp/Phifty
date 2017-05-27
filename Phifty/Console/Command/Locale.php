<?php
namespace Phifty\Console\Command;

use CLIFramework\Command;
use Phifty\FileUtils;
use Symfony\Component\Finder\Finder;
use Phifty\Kernel;

/**
 * XXX: deprecated, but reserved.
 * Export Locale
 */
class Locale extends Command
{
    public function run()
    {
        $kernel      = kernel();

        $frameworkId = $kernel::FRAMEWORK_ID;
        $appId       = $kernel->config->framework->ApplicationID;

        /* merge/update framework locale into app locale dir */
        $finder = Finder::create()->files()->name('*.po')->in(PH_ROOT . DIRECTORY_SEPARATOR . 'locale');
        $itr = $finder->getIterator();
        foreach ($itr as $item) {
            # echo $item->getPathname(). "\n";
            $sourceDir = dirname($item->getPathname());
            $sourceRelPath = FileUtils::remove_base($item->getPathname(), PH_ROOT);
            $sourceRelDir = dirname($sourceRelPath);

            $targetDir = PH_APP_ROOT . DIRECTORY_SEPARATOR . $sourceRelDir;
            FileUtils::mkpath($targetDir);

            $sourcePo = $sourceDir . DIRECTORY_SEPARATOR . $frameworkId . '.po';
            $targetPo = $targetDir . DIRECTORY_SEPARATOR . $appId . '.po';

            # var_dump( $sourcePo , $targetPo );

            if (file_exists($targetPo)) {
                $this->log("Msgcat " . basename($sourcePo) . ' => ' . basename($targetPo));
                $merged = '';
                $h = popen("msgcat $sourcePo $targetPo", 'r');
                while (!feof($h)) {
                    // send the current file part to the browser
                    $merged .= fread($h, 1024);
                }
                pclose($h);

                $this->log("Writing back to ");
                file_put_contents($targetPo, $merged);
            } else {
                $this->log("Copying files..");
                copy($sourcePo, $targetPo);
            }
        }
    }
}
