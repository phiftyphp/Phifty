<?php
namespace Phifty\Command\BundleCommand;
use CLIFramework\Command;
use Phifty\Console;
use Exception;
use DirectoryIterator;

class SyncCommand extends Command
{
    public function brief() { return 'get bundle'; }

    public function options($opts)
    {
        $this->parent->options($opts);
        $opts->add('rebase', 'rebase changes');
    }

    public function arguments($args)
    {
        $args->add('clone url');
    }

    public function syncDirectory($workingDir, $rebase = true) {
        $gitDir = $workingDir . DIRECTORY_SEPARATOR . '.git';
        if (is_dir($gitDir)) {
            if ($rebase) {
                passthru("git --work-tree $workingDir pull --rebase origin");
            } else {
                passthru("git --work-tree $workingDir pull origin");
            }
        }
    }

    public function execute()
    {
        if (!$this->options->{'target-dir'}) {
            throw new Exception('--target-dir option is required.');
        }


        foreach (new DirectoryIterator($this->options->{'target-dir'}) as $fileInfo) {
            if ($fileInfo->isDot() || substr($fileInfo,0,1) == '.') {
                continue;
            }

            $basename = $fileInfo->getFilename();
            $workingDir = realpath($fileInfo->getPathname());
            $gitDir = $workingDir . DIRECTORY_SEPARATOR . '.git';
            if (is_dir($gitDir)) {
                $this->logger->info("Syncing $basename");
                $this->syncDirectory($workingDir, $this->options->rebase);
            }
        }

        /*
        $list = scandir($this->options->{'target-dir'});
        var_dump( $list );
        */

        /*
        passthru("ls",$ret);
        var_dump( $ret );
        */

        /*
        if (1 || $this->options->git) {
            $targetBase = $this->options->{'target-dir'} . DIRECTORY_SEPARATOR . $basename;
            $this->logger->info("Cloning $cloneUrl into $targetBase");
        }
        */
    }
}






