<?php
namespace Phifty\Console\Command\BundleCommand;
use CLIFramework\Command;
use Phifty\Console\Application;
use Exception;
use DirectoryIterator;
use GitElephant\Repository;

class SyncCommand extends BaseCommand
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

    public function isGitRepository($workingDir)
    {
        $gitDir = $workingDir . DIRECTORY_SEPARATOR . '.git';
        return file_exists($gitDir);
    }

    public function isRepositoryDirty(Repository $repo)
    {
        $status = $repo->getWorkingTreeStatus();
        return $status->modified()->count() > 0
            || $status->added()->count() > 0
            || $status->deleted()->count() > 0;
    }

    public function syncDirectory($workingDir, $rebase = true)
    {
        $gitDir = $workingDir . DIRECTORY_SEPARATOR . '.git';

        $ret = 127;
        if (is_dir($gitDir)) {
            if ($rebase) {
                passthru("git --work-tree $workingDir pull --rebase origin", $ret);
            } else {
                passthru("git --work-tree $workingDir pull origin", $ret);
            }
        }
        if ($ret != 0) {
            return false;
        }

        $this->logger->info('Pushing to remote');
        passthru("git --work-tree $workingDir push origin", $ret);
        return $ret == 0;
    }

    public function execute()
    {
        if (!$this->options->{'target-dir'}) {
            throw new Exception('--target-dir option is required.');
        }

        $targetDir = $this->options->{'target-dir'};
        if (file_exists($targetDir . DIRECTORY_SEPARATOR . '.git')) {
            $this->logger->info("Syncing $targetDir...");
            $ret = $this->syncDirectory(realpath($targetDir), $this->options->rebase);
            if ($ret === false) {
                $this->logger->error("Syncing $basename failed...");
            }
        }

        foreach (new DirectoryIterator($this->options->{'target-dir'}) as $fileInfo) {
            if ($fileInfo->isDot() || substr($fileInfo,0,1) == '.') {
                continue;
            }

            $basename = $fileInfo->getFilename();
            $workingDir = realpath($fileInfo->getPathname());
            $gitDir = $workingDir . DIRECTORY_SEPARATOR . '.git';
            if ($this->isGitRepository($workingDir)) {
                $repo = new Repository($workingDir);

                if ($this->isRepositoryDirty($repo)) {
                    $this->logger->error("Repository $workingDir status dirty");
                    continue;
                }


                $this->logger->info("Syncing $basename...");
                $ret = $this->syncDirectory($workingDir, $this->options->rebase);
                if ($ret === false) {
                    $this->logger->error("Syncing $basename failed...");
                }
            }
        }

    }
}






