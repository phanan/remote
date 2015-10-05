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
        $this->app['config']->set('remote', require basename(__DIR__) . '/../src/config.php');
    }

    public function testConfigLoadedCorrectly()
    {
        // Log in via env config key
        $this->remote = new Remote('production', false);
        $this->assertEquals($this->remote->getConfig()['host'], '127.0.0.1');

        // Log in via config array
        $this->remote = new Remote($this->app['config']['remote']['connections']['staging'], false);
        $this->assertEquals($this->remote->getConfig()['host'], '::1');
    }

    public function testUnknownEnv()
    {
        $err = null;

        try {
            $this->remote = new Remote('lorem');
        } catch (Exception $e) {
            $err = $e;
        }

        $this->assertInstanceOf('InvalidArgumentException', $e);
    }

    public function testConnectionInitialized()
    {
        // Log in via env config key
        $this->remote = new Remote('production', false);
        $this->assertInstanceOf('Net_SFTP', $this->remote->getConnection());

        // Log in via config array
        $this->remote = new Remote($this->app['config']['remote']['connections']['staging'], false);
        $this->assertInstanceOf('Net_SFTP', $this->remote->getConnection());
    }

    public function testLoginUsingEnvKey()
    {
        $msg = '';

        try {
            $this->remote = new Remote('production');
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        // we expect the login to fail
        $this->assertTrue(starts_with($msg, 'Cannot connect to') || starts_with($msg, 'Failed to log in.'));
    }

    public function testLoginUsingConfigArray()
    {
        $msg = '';

        try {
            $this->remote = new Remote($this->app['config']['remote']['connections']['staging']);
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        // we expect the login to fail
        $this->assertTrue(starts_with($msg, 'Cannot connect to') || starts_with($msg, 'Failed to log in.'));
    }
}
