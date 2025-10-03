<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BotGuard
{
    /**
     * Known crawler fragments blocked by default.
     */
    protected const DEFAULT_BLOCKED_AGENTS = [
        'adsbot',
        'ahrefsbot',
        'applebot',
        'baiduspider',
        'bingbot',
        'bytespider',
        'censys',
        'curl',
        'dotbot',
        'duckduckbot',
        'exabot',
        'facebookexternalhit',
        'facebot',
        'gptbot',
        'googlebot',
        'ia_archiver',
        'linkedinbot',
        'mj12bot',
        'pinterestbot',
        'qwantify',
        'rogerbot',
        'seekportbot',
        'semrushbot',
        'seznambot',
        'slurp',
        'sogou',
        'twitterbot',
        'yacybot',
        'yandexbot',
        'yodaobot',
        'zoominfobot',
    ];

    /**
     * User-agent fragments treated as human browsers even if they include bot keywords.
     */
    protected const DEFAULT_ALLOWED_AGENTS = [
        'mozilla',
        'chrome',
        'safari',
        'firefox',
        'edge',
        'android',
        'iphone',
        'ipad',
        'macintosh',
        'windows nt',
        'linux',
        'samsungbrowser',
        'vivaldi',
        'opera',
    ];

    public static function shouldBlock(Request $request): bool
    {
        if (!static::isEnabled()) {
            return false;
        }

        $userAgent = (string) ($request->userAgent() ?? '');
        $userAgentLower = Str::lower($userAgent);

        if ($userAgentLower === '' && !static::allowBlankUserAgent()) {
            return true;
        }

        $isExplicitAllow = $userAgentLower !== '' && static::isAllowListed($userAgentLower, includeDefault: false);

        if ($userAgentLower !== '' && static::isBlockedAgent($userAgentLower) && !$isExplicitAllow) {
            return true;
        }

        if ($userAgentLower !== '' && static::isAllowListed($userAgentLower)) {
            return false;
        }

        if ($userAgent !== '' && static::matchesBlockedRegex($userAgent)) {
            return true;
        }

        if (static::isBlockedIp((string) $request->ip())) {
            return true;
        }

        if (static::requiresBrowserHeaders() && !static::looksLikeBrowserRequest($request)) {
            return true;
        }

        return false;
    }

    protected static function isEnabled(): bool
    {
        return static::boolConfig('block_known_bots', true);
    }

    protected static function allowBlankUserAgent(): bool
    {
        return static::boolConfig('allow_blank_user_agent', false);
    }

    protected static function requiresBrowserHeaders(): bool
    {
        return static::boolConfig('require_browser_headers', true);
    }

    protected static function isAllowListed(string $userAgentLower, bool $includeDefault = true): bool
    {
        $needles = $includeDefault ? static::allowedAgents() : static::explicitAllowedAgents();

        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($userAgentLower, $needle)) {
                return true;
            }
        }

        return false;
    }

    protected static function isBlockedAgent(string $userAgentLower): bool
    {
        foreach (static::blockedAgents() as $needle) {
            if ($needle !== '' && str_contains($userAgentLower, $needle)) {
                return true;
            }
        }

        return false;
    }

    protected static function matchesBlockedRegex(string $userAgent): bool
    {
        foreach (static::blockedAgentPatterns() as $pattern) {
            if ($pattern === '') {
                continue;
            }

            if (@preg_match($pattern, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    protected static function isBlockedIp(?string $ip): bool
    {
        if (!$ip) {
            return false;
        }

        foreach (static::blockedIps() as $blocked) {
            if ($blocked === '') {
                continue;
            }

            if ($blocked === $ip) {
                return true;
            }

            if (str_contains($blocked, '*')) {
                $regex = '/^' . str_replace('\\*', '.*', preg_quote($blocked, '/')) . '$/i';
                if (preg_match($regex, $ip)) {
                    return true;
                }
                continue;
            }

            if (str_contains($blocked, '/')) {
                if (static::ipMatchesCidr($ip, $blocked)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected static function looksLikeBrowserRequest(Request $request): bool
    {
        $accept = Str::lower((string) $request->headers->get('accept', ''));

        if ($accept === '') {
            return false;
        }

        $hasImageType = Str::contains($accept, 'image/');
        $hasWildcard = Str::contains($accept, '*/*');

        if (!$hasImageType && !$hasWildcard) {
            return false;
        }

        $secFetchDest = Str::lower((string) $request->headers->get('sec-fetch-dest', ''));
        if ($secFetchDest !== '' && $secFetchDest !== 'image') {
            return false;
        }

        $secFetchMode = Str::lower((string) $request->headers->get('sec-fetch-mode', ''));
        if ($secFetchMode !== '' && !in_array($secFetchMode, ['no-cors', 'cors', 'navigate'], true)) {
            return false;
        }

        return true;
    }

    protected static function blockedAgents(): array
    {
        $custom = static::extractTokens(static::configValue('blocked_user_agents'), true);
        $includeDefault = static::boolConfig('include_default_bot_list', true);
        $pool = $includeDefault ? array_merge(static::DEFAULT_BLOCKED_AGENTS, $custom) : $custom;

        return static::uniqueTokens($pool, true);
    }

    protected static function allowedAgents(): array
    {
        $pool = array_merge(static::defaultAllowedAgents(), static::explicitAllowedAgents());

        return static::uniqueTokens($pool, true);
    }

    protected static function explicitAllowedAgents(): array
    {
        $customAllowed = static::extractTokens(static::configValue('allowed_user_agents'), true);
        $trusted = static::extractTokens(static::configValue('trusted_user_agents'), true);

        return static::uniqueTokens(array_merge($customAllowed, $trusted), true);
    }

    protected static function defaultAllowedAgents(): array
    {
        return static::uniqueTokens(static::DEFAULT_ALLOWED_AGENTS, true);
    }

    protected static function blockedAgentPatterns(): array
    {
        return static::uniqueTokens(static::extractTokens(static::configValue('blocked_user_agent_regex'), false), false);
    }

    protected static function blockedIps(): array
    {
        return static::uniqueTokens(static::extractTokens(static::configValue('blocked_ips'), false), false);
    }

    protected static function boolConfig(string $key, bool $default): bool
    {
        $value = static::configValue($key);

        if (is_null($value)) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            $normalized = trim(Str::lower($value));
            if ($normalized === '') {
                return $default;
            }
            return in_array($normalized, ['1', 'true', 'on', 'yes', 'y'], true);
        }

        return $default;
    }

    protected static function configValue(string $key, $default = null)
    {
        foreach ([
            "site.security.$key",
            "settings.site.security.$key",
            "security.$key",
        ] as $path) {
            $value = config($path);
            if (!is_null($value)) {
                return $value;
            }
        }

        return $default;
    }

    protected static function extractTokens($source, bool $lowercase): array
    {
        $tokens = [];

        $collect = function ($value) use (&$collect, &$tokens, $lowercase): void {
            if (is_null($value)) {
                return;
            }

            if (is_array($value)) {
                foreach ($value as $item) {
                    $collect($item);
                }
                return;
            }

            if (is_string($value) || is_numeric($value)) {
                $token = trim((string) $value);
                if ($token === '') {
                    return;
                }

                $tokens[] = $lowercase ? Str::lower($token) : $token;
            }
        };

        if (is_string($source)) {
            $trimmed = trim($source);
            if ($trimmed === '') {
                return [];
            }

            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $collect($decoded);
            } else {
                $parts = preg_split('/[\r\n,]+/', $trimmed);
                $collect($parts);
            }
        } else {
            $collect($source);
        }

        return $tokens;
    }

    protected static function uniqueTokens(array $tokens, bool $lowercase): array
    {
        $unique = [];

        foreach ($tokens as $token) {
            if (!is_string($token) && !is_numeric($token)) {
                continue;
            }

            $value = trim((string) $token);
            if ($value === '') {
                continue;
            }

            $key = $lowercase ? Str::lower($value) : $value;
            $unique[$key] = $key;
        }

        return array_values($unique);
    }

    protected static function ipMatchesCidr(string $ip, string $cidr): bool
    {
        if (!str_contains($cidr, '/')) {
            return false;
        }

        [$subnet, $mask] = explode('/', $cidr, 2);
        $mask = trim($mask);
        $subnet = trim($subnet);

        if ($subnet === '' || $mask === '') {
            return false;
        }

        if (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $maskBits = (int) $mask;
            if ($maskBits < 0 || $maskBits > 32) {
                return false;
            }

            if ($maskBits === 0) {
                return true;
            }

            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);

            if ($ipLong === false || $subnetLong === false) {
                return false;
            }

            $maskLong = -1 << (32 - $maskBits);

            return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
        }

        if (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $maskBits = (int) $mask;
            if ($maskBits < 0 || $maskBits > 128) {
                return false;
            }

            if ($maskBits === 0) {
                return true;
            }

            $ipBinary = inet_pton($ip);
            $subnetBinary = inet_pton($subnet);

            if ($ipBinary === false || $subnetBinary === false) {
                return false;
            }

            $bytes = intdiv($maskBits, 8);
            $remainder = $maskBits % 8;

            if ($bytes > 0 && substr($ipBinary, 0, $bytes) !== substr($subnetBinary, 0, $bytes)) {
                return false;
            }

            if ($remainder === 0) {
                return true;
            }

            $maskByte = ~((1 << (8 - $remainder)) - 1) & 0xFF;

            return (ord($ipBinary[$bytes]) & $maskByte) === (ord($subnetBinary[$bytes]) & $maskByte);
        }

        return false;
    }
}
