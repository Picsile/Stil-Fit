<?php

namespace app\components;

/**
 * SOCKS5-прокси для внешних API (APIMart и т.п.).
 *
 * Локально (OSPanel): ssh -D 1080 ... → SOCKS_PROXY=127.0.0.1:1080
 * Docker на сервере: туннель на хосте → SOCKS_PROXY=host.docker.internal:1080
 */
class ProxyCurl
{
    public static function isEnabled(): bool
    {
        $value = getenv('USE_SOCKS_PROXY');
        if ($value === false) {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function getAddress(): string
    {
        $proxy = getenv('SOCKS_PROXY');
        if ($proxy !== false && $proxy !== '') {
            return $proxy;
        }

        return '127.0.0.1:1080';
    }

    /**
     * @param array<int, mixed> $options
     * @return array<int, mixed>
     */
    public static function apply(array $options): array
    {
        if (!self::isEnabled()) {
            return $options;
        }

        $options[CURLOPT_PROXY] = self::getAddress();
        $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5_HOSTNAME;

        return $options;
    }
}
