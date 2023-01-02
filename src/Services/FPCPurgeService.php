<?php

declare(strict_types=1);

namespace WeDevelop\FPCPurge;

final class FPCPurgeService
{
    public static function purge(bool $fireAndForget = true): void
    {
        if (!FPCPurgeConfig::isEnabled()) {
            return;
        }

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);

        foreach (FPCPurgeConfig::getEndpoints() as $key => $endpoint) {
            $httpHost = $endpoint['http_host'] ?? parse_url($endpoint['host'])['host'];

            $request = "{$endpoint['method']} {$endpoint['path']} HTTP/1.1\r\n";
            $request .= "Host: {$httpHost}\r\n";
            $request .= "Content-Length: 0\r\n";
            $request .= "Connection: close\r\n";
            $request .= "\r\n";

            $socket = @stream_socket_client($endpoint['host'], $errno, $errstr, (float)($endpoint['timeout'] ?? 5), STREAM_CLIENT_CONNECT, $context);

            if (!is_resource($socket)) {
                throw new \RuntimeException('Failed to purge cache: could not connect to server - ' . $errno . ' - ' . $errstr);
            }

            fwrite($socket, $request);

            if (!$fireAndForget) {
                $responseCode = stream_get_contents($socket, 3, 9);

                if ($responseCode <> 200) {
                    fclose($socket);
                    throw new \RuntimeException('Failed to purge cache: response code ' . $responseCode . ' for endpoint ' . $key . '.');
                }
            }

            fclose($socket);
        }
    }
}
