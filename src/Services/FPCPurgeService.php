<?php

declare(strict_types=1);

namespace WeDevelop\FPCPurge;

use SilverStripe\Control\Director;

final class FPCPurgeService
{
    public static function purge(): void
    {
        if (!FPCPurgeConfig::isEnabled()) {
            return;
        }

        $url = Director::absoluteURL(FPCPurgeConfig::getPurgeCacheUrl());
        $requestMethod = FPCPurgeConfig::getPurgeRequestMethod();

        $urlParts = parse_url($url);
        $urlParts['path'] ??= '/';
        $urlParts['port'] = $urlParts['port'] ?? $urlParts['scheme'] === 'https' ? 443 : 80;

        $request = "{$requestMethod} {$urlParts['path']} HTTP/1.1\r\n";
        $request .= "Host: {$urlParts['host']}\r\n";
        $request .= "Content-Length: 0\r\n";
        $request .= "\r\n";

        $prefix = substr($url, 0, 8) === 'https://' ? 'tls://' : '';

        $socket = fsockopen($prefix . $urlParts['host'], $urlParts['port']);
        fwrite($socket, $request);
        fclose($socket);
    }
}
