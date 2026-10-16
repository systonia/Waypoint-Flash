---
title: Testing
description: Check flash messages with Waypoint's testing kit.
order: 40
---

Waypoint's `TestCase` sends requests through the app without a server, so the plugin's middleware, endpoint and view helper run as they do in production. A message crosses a redirect the same way: take the `flash` cookie from the redirect and send it with the next request.

```php
<?php

use Waypoint\Flash\FlashPlugin;
use Waypoint\Options\{CsrfOptions, RendererOptions};
use Waypoint\Testing\TestCase;

final class ProfileFlashTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $app = $this->app();
        $app->plugin(new FlashPlugin());
        $app->configure(function (RendererOptions $renderer, CsrfOptions $csrf) {
            $renderer->directory = __DIR__ . '/../views';
            $renderer->layout = '_Layout.php';
            $csrf->secret = str_repeat('t', 32);
        });
        $app->attach([ProfileController::class]);
    }

    public function testSavingShowsASuccessMessageOnce(): void
    {
        $cookie = $this->request('POST', '/profile')->withCsrf()->withBody(['name' => 'Ada'])->send()
            ->assertRedirect('/profile', 303)
            ->cookie('flash');

        $page = $this->request('GET', '/profile')->withCookie('flash', (string) $cookie)->send()->assertOk();
        $page->assertSee('wp-flash-success')->assertSee('Saved, Ada.');
        $this->assertSame('', $page->cookie('flash'));   // shown, so cleared

        $this->get('/profile')->assertDontSee('Saved, Ada.');
    }

    public function testAnEmptyNameIsAnError(): void
    {
        $cookie = $this->request('POST', '/profile')->withCsrf()->withBody(['name' => ''])->send()->cookie('flash');

        $this->request('GET', '/profile')->withCookie('flash', (string) $cookie)->send()
            ->assertSee('role="alert"')
            ->assertSee('Please enter your name.');
    }
}
```

- `cookie('flash')` is the value the response sets: the encoded messages, `''` when it clears the cookie, `null` when it does not touch it.
- Use a CSRF secret of at least 32 bytes, or every signed flash cookie fails with an internal error.

To check what a controller queued without rendering anything, decode the cookie's payload, the part before the `.`:

```php
$payload = explode('.', (string) $response->cookie('flash'))[0];
$messages = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
$this->assertSame([['type' => 'success', 'text' => 'Saved, Ada.']], $messages);
```
