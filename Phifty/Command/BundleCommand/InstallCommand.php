<?php
namespace Phifty\Command\BundleCommand;
use CLIFramework\Command;
use Phifty\Console;
use Exception;
use DirectoryIterator;
use GitElephant\Repository;

class InstallCommand extends BaseCommand
{
    public function brief() { return 'install bundles'; }

    public function options($opts)
    {
        $this->parent->options($opts);
    }

    public function execute()
    {
        $sources = kernel()->config->get('framework','Services.BundleService.Sources');
        foreach ($sources as $source) {
            $get = $this->createCommand('\Phifty\Command\BundleCommand\GetCommand');
            if ($optTargetDir = $this->optionSpecs->find('target-dir')) {
                $get->options['target-dir'] = clone $optTargetDir;
                $get->options['target-dir']->setValue($source['into']);
                $get->executeWrapper([ $source['from'] ]);
            }
        }
    }
}






