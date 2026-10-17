---
title: Testing
description: Check flash messages with Waypoint's testing kit.
order: 40
---

This page tests the order form of the [first app](../index.md#a-first-app). Waypoint's `TestCase` sends requests through the app without a server, so the plugin's middleware, endpoint and view helper run as they do in production. A message crosses a redirect the same way: take the `flash` cookie from the redirect and send it with the next request.

```php
<?php

use Waypoint\Flash\FlashPlugin;
use Waypoint\Options\{CsrfOptions, RendererOptions};
use Waypoint\Testing\TestCase;

final class OrderFlashTest extends TestCase
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
        $app->attach([OrderController::class]);
    }

    public function testOrderingShowsTheThanksOnce(): void
    {
        $cookie = $this->request('POST', '/order')->withCsrf()->withBody(['name' => 'Mia'])->send()
            ->assertRedirect('/order', 303)
            ->cookie('flash');

        $page = $this->request('GET', '/order')->withCookie('flash', (string) $cookie)->send()->assertOk();
        $page->assertSee('wp-flash-success')->assertSee('Thank you, Mia! Your order is reserved.');
        $this->assertSame('', $page->cookie('flash'));   // shown, so cleared

        $this->get('/order')->assertDontSee('Thank you, Mia! Your order is reserved.');
    }

    public function testAnEmptyNameIsAnError(): void
    {
        $cookie = $this->request('POST', '/order')->withCsrf()->withBody(['name' => ''])->send()->cookie('flash');

        $this->request('GET', '/order')->withCookie('flash', (string) $cookie)->send()
            ->assertSee('role="alert"')
            ->assertSee('Please enter your name.');
    }
}
```

- `cookie('flash')` is the value the response sets: the encoded messages, `''` when it clears the cookie, `null` when it does not touch it.
- The bakery example app checks its thanks the same way, after placing an order in its test database (from `tests/Controllers/ShopControllerTest.php`):

  ```php
  $thanks = $this->get("/orders/$code/thanks")->assertRedirect("/orders/$code", 303);
  $this->request('GET', "/orders/$code")->withCookie('flash', $thanks->cookie('flash') ?? '')->send()
      ->assertOk()
      ->assertSee('Thank you, Mia! Your order is reserved.');
  ```

- Use a CSRF secret of at least 32 bytes, or every signed flash cookie fails with an internal error.

To check what a controller queued without rendering anything, decode the cookie's payload, the part before the `.`:

```php
$payload = explode('.', (string) $response->cookie('flash'))[0];
$messages = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
$this->assertSame([['type' => 'success', 'text' => 'Thank you, Mia! Your order is reserved.']], $messages);
```
