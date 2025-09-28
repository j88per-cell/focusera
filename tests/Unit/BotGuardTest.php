<?php

namespace Tests\Unit;

use App\Support\BotGuard;
use Illuminate\Http\Request;
use Tests\TestCase;

class BotGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'site.security.block_known_bots' => true,
            'site.security.include_default_bot_list' => true,
            'site.security.blocked_user_agents' => [],
            'site.security.blocked_user_agent_regex' => [],
            'site.security.allowed_user_agents' => [],
            'site.security.trusted_user_agents' => [],
            'site.security.allow_blank_user_agent' => false,
            'site.security.require_browser_headers' => true,
            'site.security.blocked_ips' => [],
        ]);
    }

    public function test_blocks_known_bot_user_agent(): void
    {
        $request = Request::create(
            '/media/photos/1',
            'GET',
            server: [
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
                'HTTP_ACCEPT' => 'image/webp,image/apng,image/*,*/*;q=0.8',
            ],
        );

        $this->assertTrue(BotGuard::shouldBlock($request));
    }

    public function test_allows_standard_browser_user_agent(): void
    {
        $request = Request::create(
            '/media/photos/1',
            'GET',
            server: [
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0 Safari/537.36',
                'HTTP_ACCEPT' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
            ],
        );

        $this->assertFalse(BotGuard::shouldBlock($request));
    }

    public function test_blocks_blank_user_agent_when_disallowed(): void
    {
        $request = Request::create(
            '/media/photos/1',
            'GET',
            server: [
                'HTTP_ACCEPT' => 'image/avif,image/webp,*/*',
                'REMOTE_ADDR' => '203.0.113.5',
            ],
        );

        $this->assertTrue(BotGuard::shouldBlock($request));
    }

    public function test_respects_allowed_user_agent_overrides(): void
    {
        config([
            'site.security.blocked_user_agents' => ['curl'],
            'site.security.allowed_user_agents' => ['curl'],
        ]);

        $request = Request::create(
            '/media/photos/1',
            'GET',
            server: [
                'HTTP_USER_AGENT' => 'curl/8.6.0',
                'HTTP_ACCEPT' => '*/*',
            ],
        );

        $this->assertFalse(BotGuard::shouldBlock($request));
    }

    public function test_blocks_listed_ip_address(): void
    {
        config(['site.security.blocked_ips' => ['198.51.100.0/24']]);

        $request = Request::create(
            '/media/photos/1',
            'GET',
            server: [
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0 Safari/537.36',
                'HTTP_ACCEPT' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                'REMOTE_ADDR' => '198.51.100.42',
            ],
        );

        $this->assertTrue(BotGuard::shouldBlock($request));
    }
}
