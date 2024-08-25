<?php

namespace Soap\Jongman\Core;

use Illuminate\Config\Repository;

class JongmanFactory
{
    protected static $config = null;

    public static function getConfig()
    {
        if (self::$config === null) {
            if (self::isLaravel()) {
                self::$config = app('config');
            } else {
                $configPath = realpath(__DIR__.'/../../../config');
                self::$config = new Repository(require $configPath.'/jongman.php');
            }

            return self::$config;
        }
    }

    public static function isLaravel()
    {
        return app() !== null && app() instanceof \Illuminate\Foundation\Application;
    }
}
