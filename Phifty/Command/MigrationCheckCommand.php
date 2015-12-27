<?php
namespace Phifty\Command;
use CLIFramework\Command;

class MigrationCheckCommand extends Command
{
    public function brief()
    {
        return 'Checking migration notes';
    }

    public function usage()
    {
        return 'phifty migrationcheck ';
    }

    public function execute()
    {
        $logger = $this->logger;
        $found = false;
        $k = kernel();

        /**
         * Checking config->View options to ViewService->
         */
        if ($k->config->framework->View) {
            $found = true;
            $this->logger->warn("View config is moved to Service.ViewService.");
        }

        if ($k->config->framework->Locale) {
            $found = true;
            $this->logger->warn("Locale config is moved to Service.LocaleService.");
        }

        if ($found) {
            $this->logger->info("Migration note not found.");
        }

    }
}
