<?php
/*
 * This file is part of the Phifty package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Phifty;
use CLIFramework\Application;

class Console extends Application
{
    const name = 'phifty';

    public function getVersion()
    {
        return Kernel::VERSION;
    }

    public function init()
    {
        parent::init();
        $this->command('init');
        // $this->command('create');
        $this->command('build-conf');
        $this->command('export');
        $this->command('locale');
        $this->command('console');
        $this->command('server');
        $this->command('router');
        $this->command('asset');

        $this->command('composer:config','Phifty\\Command\\ComposerConfigCommand');

        $this->command('new','Phifty\\Command\\GenerateCommand');
        $this->command('migration-check','Phifty\\Command\\MigrationCheckCommand');
        $this->command('check');
        $this->command('cache:clean','Phifty\\Command\\CacheCleanCommand');

        $this->command('build-schema','LazyRecord\Command\BuildSchemaCommand');
        $this->command('build-sql','LazyRecord\Command\BuildSqlCommand');
    }

    public static function getInstance()
    {
        static $instance;
        if ( $instance )

            return $instance;
        return $instance = new static;
    }
}
