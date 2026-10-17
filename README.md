# waypoint-flash

Flash messages for [Waypoint](https://github.com/Systonia/Waypoint): queue a message, redirect, and it is shown once on
the next page. Works without JavaScript; with `waypoint.js` it also auto-hides and can be dismissed
in place.

**Documentation:** [docs/](docs/index.md), with [guides](docs/guides/index.md) and the [reference](docs/reference/index.md).

## Setup

```php
$app->plugin(new Waypoint\Flash\FlashPlugin());   // before attach()
```

In the layout (shortened from `views/_Layout.php` of the [bakery example app](https://github.com/systonia/Waypoint-Bakery)):

```html
<body class="min-h-screen bg-stone-50 text-stone-900">
{{ $this->flashes() }}
<header>...</header>
<main class="mx-auto max-w-5xl px-4 py-8">{{ $content }}</main>
{{ $this->waypointJsTag() }}
{{ $this->pluginAssetTags() }}
</body>
```

## Usage

The bakery thanks its customer after the checkout and sends them on to their order (from `src/Controllers/ShopController.php`):

```php
#[Inject]
private Waypoint\Flash\Flash $flash;

#[Get('/orders/{code}/thanks')]
public function thanks(string $code): Redirect
{
    $order = $this->orders->byCode($code) ?? throw new NotFoundException('No such order');
    $this->flash->success("Thank you, {$order->customerName}! Your order is reserved.");   // also info(), warning(), error()
    return new Redirect("/orders/{$order->code}", 303);
}
```

A message queued while rendering a page (no redirect) is shown on that page. Text is escaped;
messages carry no HTML.

## How it works

- Queued messages travel in the `flash` cookie (base64url JSON, signed with `CsrfOptions::$secret`
  when configured); the next rendered page reads it and clears it.
- `$this->flashes()` returns a `Waypoint\Html`: `<div class="wp-flashes" aria-live="polite">` with one block per
  message: `role="status"`, or `role="alert"` for errors, plus a real dismiss form.
- Without JavaScript the dismiss button posts to `/flash/dismiss`, which drops pending messages and
  returns to the referring page. With JavaScript `flash.js` removes the message in place, hides
  non-error messages after `timeout`, and moves focus to an error.

## Options

| Setting | Default | Description |
|---|---|---|
| `cookieName` | `'flash'` | Cookie carrying queued messages. |
| `ttl` | `120` | Seconds an unread message survives. |
| `cookieSecure` | `null` | Cookie Secure attribute; `null` follows the request (`Request::$https`). |
| `dismissPath` | `'/flash/dismiss'` | The no-JS dismiss endpoint. |
| `timeout` | `6000` | Milliseconds until the client hides a message; `0` keeps it. Errors always stay. |
| `dismissLabel` | `'Dismiss'` | `aria-label` of the dismiss button. |

## Development

```bash
composer install && composer test && composer stan
```

## License

MIT or Apache-2.0, at your option.
