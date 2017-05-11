<?php

namespace Sethorax\TYPO3TERWebHook;

use Symfony\Component\Yaml\Yaml;

class ConfigUtility
{
    public static function getConfig()
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/../config.yml'));
    }

    public static function validateConfig()
    {
        $config = self::getConfig();

        if (!isset($config['authorization']['github']['secret'])) {
            return false;
        }

        if (!isset($config['authorization']['typo3']['username'])) {
            return false;
        }

        if (!isset($config['authorization']['typo3']['password'])) {
            return false;
        }

        if (!isset($config['notification']['slack']['webhook-url'])) {
            return false;
        }

        return true;
    }
}
