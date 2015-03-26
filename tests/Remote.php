<?php namespace PhanAn\Remote;

use Illuminate\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Config\Repository;
use PhanAn\Remote\Remote;
use Net_SFTP;

class RemoteTest extends \PHPUnit_Framework_TestCase {

    var $app;
    var $remote;

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

        // init the remote connection without logging in
        $this->remote = new Remote('production', FALSE);
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
        try {
            $this->remote = new Remote('staging');
        } catch (\Exception $e) {
            // we expect the login to fail
            $this->assertStringStartsWith('Cannot connect to', $e->getMessage());
        }
    }

}
