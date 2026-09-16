<?php

namespace Waypoint\Flash\Tests;

use Waypoint\Flash\{FlashOptions, FlashPlugin};
use Waypoint\Options\{CsrfOptions, RendererOptions};
use Waypoint\Testing\TestCase;

final class PluginTest extends TestCase
{
    private function boot(bool $signed = true): void
    {
        $app = $this->app();
        $app->plugin(new FlashPlugin());
        $app->configure(function (RendererOptions $opts) {
            $opts->directory = __DIR__ . '/views';
            $opts->layout = '_Layout.php';
        });
        $app->configure(function (FlashOptions $opts) {
            $opts->cookieSecure = false;
        });
        if ($signed) {
            $app->configure(function (CsrfOptions $opts) {
                $opts->secret = 'flash-test-secret';
            });
        }
        $app->attach([FlashController::class]);
    }

    public function testAMessageSurvivesTheRedirectIsShownOnceAndThenGone(): void
    {
        $this->boot();

        $cookie = $this->post('/save')->assertRedirect('/page')->assertCookie('flash')->cookie('flash');

        $page = $this->request('GET', '/page')->withCookie('flash', (string) $cookie)->send()->assertOk();
        $page->assertSee('class="wp-flash wp-flash-success" role="status" data-timeout="6000"')
            ->assertSee('Saved &lt;b&gt;ok&lt;/b&gt;')
            ->assertSee('action="/flash/dismiss"')
            ->assertSee('<link rel="stylesheet" href="/assets/flash.flash.');
        $this->assertSame('', $page->cookie('flash'), 'the cookie is cleared once the message was shown');

        $this->get('/page')->assertDontSee('wp-flash ');
    }

    public function testErrorsAreAlertsWithoutTimeout(): void
    {
        $this->boot();
        $cookie = $this->post('/fail')->cookie('flash');

        $this->request('GET', '/page')->withCookie('flash', (string) $cookie)->send()
            ->assertSee('class="wp-flash wp-flash-error" role="alert"><span');
    }

    public function testAPartialNavigationGetsTheMessagesAsAnOutOfBandFragment(): void
    {
        $this->boot();
        $cookie = $this->post('/save')->cookie('flash');

        $partial = $this->request('GET', '/page')->asPartial()->withCookie('flash', (string) $cookie)->send()->assertOk();
        $partial->assertSee('<template wp-swap-oob="flash"><div class="wp-flash wp-flash-success"')
            ->assertDontSee('<html');
        $this->assertSame('', $partial->cookie('flash'));
    }

    public function testARedirectOrJsonResponseNeverConsumesTheMessage(): void
    {
        $this->boot();
        $cookie = (string) $this->post('/save')->cookie('flash');

        $api = $this->request('GET', '/api')->withCookie('flash', $cookie)->send()->assertOk();
        $api->assertDontSee('wp-flash');
        $this->assertStringContainsString('Saved', self::decode((string) $api->cookie('flash')), 'still waiting for an HTML page, re-sent with a fresh TTL');

        $again = $this->request('POST', '/fail')->asPartial()->withCookie('flash', $cookie)->send()->assertRedirect('/page');
        $this->assertStringContainsString('Nope', self::decode((string) $again->cookie('flash')), 'the new message is added, nothing is rendered into the redirect');
        $this->assertStringContainsString('Saved', self::decode((string) $again->cookie('flash')));
    }

    private static function decode(string $cookie): string
    {
        return (string) base64_decode(strtr(explode('.', $cookie)[0], '-_', '+/'));
    }

    public function testAMessageQueuedWhileRenderingShowsImmediately(): void
    {
        $this->boot();

        $this->get('/now')->assertSee('wp-flash-info')->assertSee('Right away');
    }

    public function testDismissEndpointClearsTheCookieAndGoesBack(): void
    {
        $this->boot();
        $cookie = (string) $this->post('/save')->cookie('flash');

        $response = $this->request('POST', '/flash/dismiss')->withCookie('flash', $cookie)
            ->withHeader('Host', 'app.test')->withHeader('Referer', 'http://app.test/page?x=1')->send();
        $response->assertRedirect('/page?x=1', 303);
        $this->assertSame('', $response->cookie('flash'));

        $this->request('POST', '/flash/dismiss')->withHeader('Host', 'app.test')->withHeader('Referer', 'http://evil.test/steal')->send()->assertRedirect('/', 303);
    }

    public function testAForgedCookieIsIgnoredWhenSigningIsConfigured(): void
    {
        $this->boot();
        $forged = rtrim(strtr(base64_encode('[{"type":"success","text":"forged"}]'), '+/', '-_'), '=') . '.bad';

        $this->request('GET', '/page')->withCookie('flash', $forged)->send()->assertDontSee('forged');
    }

    public function testWithoutASecretTheCookieIsPlainButStillWorks(): void
    {
        $this->boot(signed: false);
        $cookie = (string) $this->post('/save')->cookie('flash');

        $this->assertStringNotContainsString('.', $cookie);
        $this->request('GET', '/page')->withCookie('flash', $cookie)->send()->assertSee('wp-flash-success');
    }
}
