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
    private static string $purge_cache_url = '/purge-cache';

    /**
     * @config
     */
    private static string $purge_request_method = 'PURGE';

    public static function isEnabled(): bool
    {
        return (bool)Config::inst()->get(self::class, 'enabled');
    }

    public static function getPurgeCacheUrl(): string
    {
        return (string)Config::inst()->get(self::class, 'purge_cache_url');
    }

    public static function getPurgeRequestMethod(): string
    {
        return (string)Config::inst()->get(self::class, 'purge_request_method');
    }
}
