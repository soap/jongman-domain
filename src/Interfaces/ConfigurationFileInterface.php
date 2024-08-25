<?php

namespace Soap\Jongman\Core\Interfaces;

interface ConfigurationFileInterface
{
    public function get($key): array;
}
