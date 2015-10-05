<?php

namespace PhanAn\Remote;

use Exception;
use InvalidArgumentException;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;

class Remote
{
    /**
     * The configuration array of the current connection/environment.
     *
     * @see  config/remote.php
     *
     * @var array
     */
    private $config;

    /**
     * The SSH object. The main horse. The unsung hero.
     *
     * @var \phpseclib\Net\SSH2
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
     * @param string|array $connection Key of the connection (see config/remote.php),
     *                                 or the connection config array.
     * @param bool         $auto_login Should we try logging in right away?
     *
     * @return void
     */
    public function __construct($env = '', $auto_login = true)
    {
        // If the user is supplying an array, we assume it to be the configuration array and will just use it directly.
        if (is_array($env)) {
            $this->config = $env;
        }
        // Otherwise, we rely on the config array found in config/remote.php
        else {
            $env = $env ?: config('remote.default');

            if (!$this->config = config("remote.connections.$env")) {
                throw new InvalidArgumentException("No configuration found for `$env` server.");
            }
        }

        $this->ssh = new SSH2($this->config('host'), $this->config('port'));

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
            $key = new RSA();

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
        return array_get($this->config, $key);
    }

    /**
     * Get the SSH connection.
     *
     * @return \phpseclib\Net\SSH2
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
        return $this->config;
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
