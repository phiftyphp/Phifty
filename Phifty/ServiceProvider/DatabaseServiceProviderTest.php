<?php

namespace Phifty\ServiceProvider;

use Phifty\Kernel;
use PHPUnit\Framework\TestCase;

use Maghead\Manager\DataSourceManager;

class DatabaseServiceProviderTest extends TestCase
{
    public function testLoadDatabaseServiceConfig()
    {
        $serviceProvider = new DatabaseServiceProvider();

        $kernel = new \App\AppKernel;

        $serviceProvider->register($kernel, [
            'config' => 'config/database.yml',
        ]);

        $dsManager = DataSourceManager::getInstance();
        $this->assertNotNull($dsManager->getConnection('master'));
        $this->assertNotNull($dsManager->getMasterConnection());
    }
}
