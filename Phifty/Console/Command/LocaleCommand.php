<?php
namespace Phifty\Console\Command;
use CLIFramework\Command;
use Symfony\Component\Finder\Finder;
use Phifty\Kernel;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Phifty\FileUtils;
use Phifty;


/*
function proc_read($command)
{
    $output = '';
    $h = popen($command,'r');
    while (!feof($h)) {
        $content .= fread($h, 1024);
    }
    pclose($h);
    return $output;
}
*/


/**
 *
 * 1. create dictionary from locale files (po files)
 * 2. scan PHP files and look for _( ) and __( ) pattern
 * 3. build & scan twig templates
 * 4. rewrite po files
 *
 */
class LocaleCommand extends Command
{

    public function options($opts)
    {
        $opts->add('f|force','force');
    }

    public function init()
    {
        parent::init();
        $this->command('parse','Phifty\Console\Command\LocaleParseCommand');
        $this->command('update','Phifty\Console\Command\LocaleUpdateCommand');
    }

    public function execute()
    {
        $parse = $this->createCommand('Phifty\Console\Command\LocaleParseCommand');
        $parse->options = $this->options;
        $parse->executeWrapper(array());

        $update = $this->createCommand('Phifty\Console\Command\LocaleUpdateCommand');
        $update->options = $this->options;
        $update->executeWrapper(array());
    }

}
