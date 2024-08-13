<?php

namespace Soap\Jongman\Core\Application\Config;

use Illuminate\Config\Repository;
use Symfony\Component\Finder\Finder;

class Config extends Repository
{
    public function loadConfig($path, $enviroment = null)
    {
        $this->configPath = $path;
        foreach ($this->getConfigurationFiles() as $fileKey => $filePath) {
            $this->set($fileKey, require $filePath);
        }

        foreach ($this->getConfigurationFiles($enviroment) as $fileKey => $filePath) {
            $envConfig = require $filePath;

            foreach ($envConfig as $envKey => $value) {
                $this->set($fileKey.' . '.$envKey, $envValue);
            }
        }
    }

    public function getConfigurationFiles($enviroment = null)
    {
        $path = $this->configPath;

        if ($environment) {
            $path .= '/'.$environment;
        }

        if (! is_dir($path)) {
            return [];
        }

        $files = [];
        $phpFiles = Finder::create()->files()->name('*.php')->in($path)->depth(0);

        foreach ($phpFiles as $file) {
            $files[basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        return $files;
    }
}
