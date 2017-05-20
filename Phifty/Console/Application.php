<?php
/*
 * This file is part of the Phifty package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Phifty\Console;

class Application extends \CLIFramework\Application
{
    const name = 'phifty';

    public function init()
    {
        parent::init();
        $this->command('init');
        $this->command('bootstrap');
        $this->command('export');
        $this->command('locale');
        $this->command('console');
        $this->command('server');
        $this->command('router');
        $this->command('asset');
        $this->command('bundle');

        $this->command('composer-config');
        $this->command('server-config');

        $this->command('new', Command\GenerateCommand::class);
        $this->command('check');
        $this->command('cache:clean', Command\CacheCleanCommand::class);
    }

    public static function getInstance()
    {
        static $instance;
        if ( $instance ) {
            return $instance;
        }
        return $instance = new static;
    }
}
