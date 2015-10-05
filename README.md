# Remote

[![Build Status](https://travis-ci.org/phanan/remote.svg?branch=master)](https://travis-ci.org/phanan/remote)
[![Dependency Status](https://gemnasium.com/phanan/remote.svg)](https://gemnasium.com/phanan/remote)
[![License](https://poser.pugx.org/phanan/remote/license.svg)](https://packagist.org/packages/phanan/remote)

*Remote* is a simple package that brings the ability to do remote connections back into Laravel 5. This package makes use of the awesome [phpseclib](https://github.com/phpseclib/phpseclib) behind the scene.

## Requirements
* PHP >= 5.4
* Anything required by phpseclib

## Installation
First, require `phanan/remote` into your `composer.json` and run `composer update`.

```
    "require": {
        "laravel/framework": "5.1.*",
        "phanan/remote": "~2.*"
    },
```

After the package is downloaded, open `config/app.php` and add its service provider class:

``` php
    'providers' => [
        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

        PhanAn\Remote\RemoteServiceProvider::class,
    ],
```

Now if you need a sample configuration file – you don’t actually, see Usage for array-based login:

``` bash
php artisan vendor:publish
```

Look for a `remote.php` file under your `config` directory and modify it to fit your needs.


## Usage
Using `Remote` is dead simple: Just initialize a `PhanAn\Remote\Remote` object, say `$connection`. The constructor accepts two arguments, none of which is required:

* `$env` (`string|array`): The key of the remote connection array found in `config/remote.php` if a string, or the configuration array itself if an array. Defaults to an empty string, in which case the default connection will be used.
* `$auto_login` (`boolean`): Whether or not the connection should attempt to log into the remote server upon class construction. Defaults to `true`.

Here's where the magic happens. Like, literally. `Remote` makes use of the magic function `__call()` to pass all unrecognized methods to the `phpseclib\Net\SFTP` object underneath. Which means, you can call any `phpseclib\Net\SFTP` method directly on a `Remote` object:

``` php
<?php

namespace App\Http\Controllers;

use Exception;
use PhanAn\Remote\Remote;

class RemoteController extends Controller
{
    public function getConnect()
    {
        $connection = new Remote();

        // Of course you can specify a configured environment name, like this
        // $connection = new Remote('staging');
        //
        // Or even an array, like this
        // $connection = new Remote([
        //     'host' => '::1',
        //     'port' => 22,
        //     'username' => 'doge',
        //     'password' => 'SoIPv6MuchModern',
        // ]);

        // All methods below are from \phpseclib\Net\SFTP, not Remote itself! Magic!

        // Create a file with some dummy content
        $connection->put('doge', 'Much remote so convenience wow.');

        // Execute a command
        $dir_content = $connection->exec('ls -a');

        // Get some standard errors
        if ($error = $connection->getStdError()) {
            throw new Exception("Houston, we have a problem: $error");
        }
    }
}

```

Check phpseclib's official [SFTP Feature List](http://phpseclib.sourceforge.net/sftp/intro.html) for details of what you can do.

## License
MIT © [Phan An](http://phanan.net)
