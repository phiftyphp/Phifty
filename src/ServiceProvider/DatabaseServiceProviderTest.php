<?php

namespace Phifty\ServiceProvider;

use Phifty\Kernel;
use Phifty\Testing\TestCase;

use Maghead\Manager\DataSourceManager;

class DatabaseServiceProviderTest extends TestCase
{
    public function testLoadDatabaseServiceConfig()
    {
        $kernel = Kernel::minimal($this->configLoader);

        $serviceProvider = new DatabaseServiceProvider();
        $serviceProvider->register($kernel, [
            'config' => 'config/database.yml',
        ]);

        $serviceProvider->boot($kernel);

        $dsManager = DataSourceManager::getInstance();
        $this->assertNotNull($dsManager->getConnection('master'));
        $this->assertNotNull($dsManager->getMasterConnection());
    }
}
