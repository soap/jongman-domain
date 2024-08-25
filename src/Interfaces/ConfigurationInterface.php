<?php

namespace Soap\Jongman\Core\Interfaces;

interface ConfigurationInterface
{
    public function register($configFile, $overwrite = false);
}
