<?php

namespace Phifty\ServiceProvider;

use Phifty\Kernel;
use PHPUnit\Framework\TestCase;

use Maghead\Manager\DataSourceManager;

class DatabaseServiceProviderTest extends TestCase
{
    public function testLoadDatabaseServiceConfig()
    {
        $serviceProvider = new DatabaseServiceProvider([
            'configPath' => 'config/database.yml',
        ]);

        $kernel = new \App\AppKernel;
        $serviceProvider->register($kernel);

        $dsManager = DataSourceManager::getInstance();
        $this->assertNotNull($dsManager->getConnection('master'));
        $this->assertNotNull($dsManager->getMasterConnection());
    }
}

