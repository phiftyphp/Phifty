<?php
namespace Phifty\Console\Command\BundleCommand;
use CLIFramework\Command;
use Phifty\Console\Application;
use Exception;

class GetCommand extends BaseCommand
{
    public function brief() { return 'get bundle'; }

    public function options($opts)
    {
        $this->parent->options($opts);
    }

    public function arguments($args)
    {
        $args->add('clone url');
    }

    public function execute($cloneUrl, $basename = null)
    {
        if (!$basename) {
            if (preg_match('/(\w+)@(.*?):(.*?)(?:.git)?$/i', $cloneUrl, $matches)) {
                list($all, $user, $host, $path) = $matches;
                $info = parse_url($path);
                $basename = basename($path);
            }
        }

        if (!$this->options->{'target-dir'}) {
            throw new Exception('--target-dir option is required.');
        }

        $targetDir = $this->options->{'target-dir'};

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetBase = $targetDir . DIRECTORY_SEPARATOR . $basename;

        if (file_exists($targetBase)) {
            $this->logger->warn("$targetBase already exists.");
            return false;
        }

        $this->logger->info("Cloning $cloneUrl into $targetBase");
        passthru("git clone $cloneUrl $targetBase", $ret);
        return $ret == 0;
    }
}






