# waypoint-flash

Flash messages for [Waypoint](https://github.com/Systonia/Waypoint): queue a message, redirect, and it is shown once on
the next page. Works without JavaScript; with `waypoint.js` it also auto-hides and can be dismissed
in place.

**Documentation:** [docs/](docs/index.md), with [guides](docs/guides/index.md) and the [reference](docs/reference/index.md).

## Setup

```php
$app->plugin(new Waypoint\Flash\FlashPlugin());   // before attach()
```

In the layout:

```php
<body>
    <?= $this->flashes() ?>
    <main><?= $content ?></main>
    <?= $this->waypointJsTag() ?>
    <?= $this->pluginAssetTags() ?>
```

## Usage

```php
#[Inject]
private Waypoint\Flash\Flash $flash;

$this->flash->success('Profile updated.');   // also info(), warning(), error()
return new Redirect('/profile');
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
| `cookieSecure` | `true` | Cookie Secure attribute; `false` for HTTP-only local dev. |
| `dismissPath` | `'/flash/dismiss'` | The no-JS dismiss endpoint. |
| `timeout` | `6000` | Milliseconds until the client hides a message; `0` keeps it. Errors always stay. |
| `dismissLabel` | `'Dismiss'` | `aria-label` of the dismiss button. |

## Development

```bash
composer install && composer test && composer stan
```

## License

MIT or Apache-2.0, at your option.
