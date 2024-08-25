<?php

namespace Soap\Jongman\Core\Application\Config;

use Illuminate\Config\Repository;
use Soap\Jongman\Core\Interfaces\ConfigurationInterface;

class Config implements ConfigurationInterface
{
    protected $_config;

    private static $_instance = null;

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new Config;
        }

        return self::$_instance;
    }

    public function register($configFile, $overwrite = false)
    {
        $this - $_config = new Repository(require $configFile);
    }
}
