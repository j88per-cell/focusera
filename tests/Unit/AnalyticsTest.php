<?php

namespace Tests\Unit;

use App\Jobs\ProcessAnalyticsEvent;
use App\Models\Gallery;
use App\Models\Photo;
use App\Models\User;
use App\Support\Analytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_records_payload_with_normalized_metadata(): void
    {
        Queue::fake();

        config([
            'site.analytics.enabled' => true,
            'site.analytics.capture_referrer' => true,
            'site.analytics.queue' => 'analytics',
        ]);

        $this->startSession();

        $gallery = Gallery::factory()->create();
        $photo = Photo::factory()->for($gallery)->create();
        $user = User::factory()->create();

        $request = Request::create(
            '/galleries/' . $gallery->id . '/photos/' . $photo->id,
            'GET',
            server: [
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148',
                'HTTP_REFERER' => 'https://ref.example.com/path',
            ],
        );

        $router = app('router');
        $router->get('galleries/{gallery}/photos/{photo}', fn () => null)->name('galleries.photos.show');
        $request->setRouteResolver(function () use ($router, $request, $gallery, $photo) {
            $route = $router->getRoutes()->match($request);
            $route->setParameter('gallery', $gallery);
            $route->setParameter('photo', $photo);

            return $route;
        });

        $session = app('session')->driver();
        $session->start();
        $request->setLaravelSession($session);
        $request->setUserResolver(fn () => $user);

        Analytics::record('private_gallery_view', ['custom' => 'value'], $request);

        Queue::assertPushedOn('analytics', ProcessAnalyticsEvent::class, function (ProcessAnalyticsEvent $job) use ($gallery, $photo, $user) {
            $payload = $job->payload;

            $this->assertSame('private_gallery_view', $payload['event']);
            $this->assertSame($session = app('session')->getId(), $payload['session_id']);
            $this->assertSame($user->id, $payload['user_id']);
            $this->assertSame('mobile', $payload['device']);
            $this->assertSame('ref.example.com', $payload['referrer']);

            $meta = $payload['meta'];
            $this->assertSame('/galleries/' . $gallery->id . '/photos/' . $photo->id, $meta['path']);
            $this->assertSame('/galleries/{id}/photos/{id}', $meta['normalized_path']);
            $this->assertSame('galleries.photos.show', $meta['route']);
            $this->assertSame('GET', $meta['method']);
            $this->assertSame($gallery->id, $meta['gallery_id']);
            $this->assertSame($photo->id, $meta['photo_id']);
            $this->assertSame('value', $meta['custom']);

            return true;
        });
    }

    public function test_does_not_queue_when_disabled(): void
    {
        Queue::fake();

        config(['site.analytics.enabled' => false]);

        $this->startSession();
        $request = Request::create('/test', 'GET');
        $session = app('session')->driver();
        $session->setId('no-analytics');
        $session->start();
        $request->setLaravelSession($session);

        Analytics::record('noop', [], $request);

        Queue::assertNothingPushed();
    }

    public function test_normalizes_uuid_segments_in_path(): void
    {
        Queue::fake();

        config(['site.analytics.enabled' => true]);

        $this->startSession();

        $uuid = '123e4567-e89b-12d3-a456-426614174000';
        $request = Request::create('/admin/orders/' . $uuid, 'GET');
        $session = app('session')->driver();
        $session->start();
        $request->setLaravelSession($session);

        Analytics::record('uuid-path', [], $request);

        Queue::assertPushed(ProcessAnalyticsEvent::class, function (ProcessAnalyticsEvent $job) {
            $this->assertSame('/admin/orders/{uuid}', $job->payload['meta']['normalized_path']);
            return true;
        });
    }
}
