<?php


namespace PhanAn\Remote;

use Crypt_RSA;
use Exception;
use InvalidArgumentException;
use Net_SFTP;

class Remote
{
    /**
     * Name of the connection/environment.
     * 
     * @see  config/remote.php
     *
     * @var string
     */
    private $env;

    /**
     * The SSH object. The main horse. The unsung hero.
     * 
     * @var \Net_SFTP
     */
    private $ssh;

    /**
     * Are we in yet?
     * 
     * @var bool
     */
    private $in = false;

    /**
     * Initialize a Remote object.
     * 
     * @param string $connection_name Name of the connection. See config/remote.php
     * @param bool   $auto_login      Should we try logging in right away? 
     */
    public function __construct($connection_name = null, $auto_login = true)
    {
        if (!$connection_name) {
            $connection_name = config('remote.default');
        }

        $this->env = $connection_name;

        if (!config("remote.connections.{$this->env}")) {
            throw new InvalidArgumentException("No configuration found for `{$this->env}` server.");
        }

        $this->ssh = new Net_SFTP($this->config('host'), $this->config('port'));

        if ($auto_login) {
            $this->login();
        }
    }

    /**
     * Log into the server.
     * 
     * @return void
     */
    public function login()
    {
        // Do nothing if already logged in
        if ($this->in) {
            return;
        }

        if ($this->config('key')) {
            // We prefer logging in via keys
            $key = new Crypt_RSA();

            if ($phrase = $this->config('keyphrase')) {
                $key->setPassword($phrase);
            }

            $key->loadKey(file_get_contents($this->config('key')));
        } else {
            // Password is less preferred, but anyway...
            $key = $this->config('password');
        }

        if (!$this->in = $this->ssh->login($this->config('username'), $key)) {
            throw new Exception('Failed to log in.');
        }
    }

    /**
     * Get a config value of the current env.
     * Just a tiny helper so that we don't need to check if a key is set.
     * 
     * @param string $key The configuration key
     *
     * @return mixed
     */
    private function config($key)
    {
        return config("remote.connections.{$this->env}.$key", null);
    }

    /**
     * Get the SSH connection.
     * 
     * @return \Net_SFTP
     */
    public function getConnection()
    {
        return $this->ssh;
    }

    /**
     * Get the config array for the current server.
     * 
     * @return array
     */
    public function getConfig()
    {
        return config("remote.connections.{$this->env}");
    }

    /**
     * Transfer any other methods to the ssh object.
     * 
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->ssh, $method], $args);
    }
}
