<?php

declare(strict_types=1);

namespace WeDevelop\FPCPurge;

use SilverStripe\Core\Config\Config;

final class FPCPurgeConfig
{
    /**
     * @config
     */
    private static bool $enabled = false;

    /**
     * @config
     */
    private static array $endpoints = [];

    public static function isEnabled(): bool
    {
        return (bool)Config::inst()->get(self::class, 'enabled');
    }

    public static function getEndpoints(): array
    {
        return (array)Config::inst()->get(self::class, 'endpoints');
    }
}
