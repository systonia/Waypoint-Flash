# waypoint-flash

Flash messages for [Waypoint](https://github.com/Systonia/Waypoint): queue a message, redirect, and it is shown once on
the next page. Works without JavaScript; with `waypoint.js` it also appears after a partial
navigation, auto-hides, and can be dismissed in place.

## Setup

```php
$app->plugin(new Waypoint\Flash\FlashPlugin());   // before attach()
```

In the layout, outside the container your pages swap into:

```php
<body>
    <?= $this->flashes() ?>
    <main wp-container="main-content"><?= $content ?></main>
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
- `$this->flashes()` renders `<div wp-container="flash" aria-live="polite">` with one block per
  message: `role="status"`, or `role="alert"` for errors, plus a real dismiss form.
- A partial (`waypoint.js`) response gets the messages appended as `<template wp-swap-oob="flash">`,
  so they land in the same region.
- Without JavaScript the dismiss button posts to `/flash/dismiss`, which drops pending messages and
  returns to the referring page. With JavaScript `flash.js` removes the message in place, hides
  non-error messages after `timeout`, and moves focus to an error after a swap.

## Options

| Setting | Default | Description |
|---|---|---|
| `cookieName` | `'flash'` | Cookie carrying queued messages. |
| `ttl` | `120` | Seconds an unread message survives. |
| `cookieSecure` | `true` | Cookie Secure attribute; `false` for HTTP-only local dev. |
| `container` | `'flash'` | The `wp-container` key of the message region. |
| `dismissPath` | `'/flash/dismiss'` | The no-JS dismiss endpoint. |
| `timeout` | `6000` | Milliseconds until the client hides a message; `0` keeps it. Errors always stay. |
| `dismissLabel` | `'Dismiss'` | `aria-label` of the dismiss button. |

## Development

```bash
composer install && composer test && composer stan
```
