<?php

namespace Phifty\ServiceProvider;

use Phifty\Testing\TestCase;
use Phifty\Kernel;

class CacheServiceProviderTest extends TestCase
{
    public function testRegisterFileSystemCacheService()
    {
        $config = ['FileSystem' => true];
        $config = CacheServiceProvider::canonicalizeConfig($this->kernel, $config);
        $provider = new CacheServiceProvider;
        $provider->register($this->kernel, $config);
        $this->assertNotNull($this->kernel->cache);
        $this->kernel->cache->set('key', 'foo');
        $this->assertEquals('foo', $this->kernel->cache->get('key'));
    }


    /**
     * @requires extension apcu
     */
    public function testRegisterApcuCacheService()
    {
        $config = [ 'APC' => true ];
        $config = CacheServiceProvider::canonicalizeConfig($this->kernel, $config);
        $provider = new CacheServiceProvider;
        $provider->register($this->kernel, $config);
        $this->assertNotNull($this->kernel->cache);
    }



}
