<?php

use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use PhanAn\Remote\Remote;

class RemoteTest extends PHPUnit_Framework_TestCase
{
    public $app;
    public $remote;

    public function setUp()
    {
        // Init an Illuminate Application
        // Set the environment to a fake foo
        // create an empty instance of Config
        $this->app = new Application();
        $this->app['env'] = 'production';
        $this->app->setBasePath(sys_get_temp_dir());
        $this->app->instance('config', new Repository());

        // load the default config
        $this->app['config']->set('remote', require basename(__DIR__).'/../src/config.php');

        // init the remote connection without logging in
        $this->remote = new Remote('production', false);
    }

    public function testConfigLoadedCorrectly()
    {
        $config = $this->remote->getConfig();
        $this->assertEquals($config['host'], '1.2.3.4');
    }

    public function testConnectionInitialized()
    {
        $this->assertInstanceOf('Net_SFTP', $this->remote->getConnection());
    }

    public function testLogin()
    {
        $msg = '';

        try {
            $this->remote = new Remote('staging');
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        // we expect the login to fail
        $this->assertStringStartsWith('Cannot connect to', $msg);
    }
}
