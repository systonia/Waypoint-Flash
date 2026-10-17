---
title: Waypoint Flash
description: "Flash messages for Waypoint: queue a message, redirect, show it once on the next page."
order: 1
---

Waypoint Flash adds one-time messages to a [Waypoint](https://github.com/systonia/Waypoint) app: a controller queues "Profile updated." and redirects, and the next page that renders shows the message once. Messages travel in a cookie, so the app needs no session. The markup works without JavaScript; with the bundled client script, messages hide on their own and close in place.

## Install

```bash
composer require systonia/waypoint-flash
```

The package needs PHP 8.5 and `systonia/waypoint`. Register the plugin before `attach()`:

```php
use Waypoint\Flash\FlashPlugin;

$app->plugin(new FlashPlugin());
```

and render the messages in the layout:

```html
<body>
    {{ $this->flashes() }}
    <main>{{ $content }}</main>
    {{ $this->waypointJsTag() }}
    {{ $this->pluginAssetTags() }}
</body>
```

`$this->flashes()` prints the messages. `pluginAssetTags()` adds the plugin's CSS and script; the script builds on `waypoint.js`, so `waypointJsTag()` comes first.

## A first app

`index.php`:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Waypoint\Attributes\{Controller, Get, Inject, NotBlank, Post};
use Waypoint\Flash\{Flash, FlashPlugin};
use Waypoint\FromArray;
use Waypoint\Http\{Redirect, Request, View};
use Waypoint\Options\{CsrfOptions, RendererOptions};
use Waypoint\Waypoint;

final class ProfileForm
{
    use FromArray;

    #[NotBlank]
    public string $name = '';
}

#[Controller('/profile')]
final class ProfileController
{
    #[Inject]
    private Flash $flash;

    #[Get('')]
    public function show(): View
    {
        return new View('Profile');
    }

    #[Post('')]
    public function save(Request $req): Redirect
    {
        $form = new ProfileForm($req->body());
        if (!$form->isValid) {
            $this->flash->error('Please enter your name.');
            return new Redirect('/profile', 303);
        }
        $this->flash->success("Saved, {$form->name}.");
        return new Redirect('/profile', 303);
    }
}

$app = Waypoint::create();
$app->plugin(new FlashPlugin());
$app->configure(function (RendererOptions $renderer, CsrfOptions $csrf) {
    $renderer->directory = __DIR__ . '/views';
    $renderer->layout = '_Layout.php';
    $csrf->secret = 'dev-secret-of-at-least-32-bytes-long';   // from Environment in a real app
});
$app->attach([ProfileController::class]);
$app->serve();
```

`views/_Layout.php`:

```html
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Profile</title>
</head>
<body>
    {{ $this->flashes() }}
    <main>{{ $content }}</main>
    {{ $this->waypointJsTag() }}
    {{ $this->pluginAssetTags() }}
</body>
</html>
```

`views/Profile.php`:

```html
<h1>Profile</h1>
<form method="post" action="/profile">
    {{ $this->csrf->field() }}
    <label>Name <input name="name"></label>
    <button>Save</button>
</form>
```

Run it with `tenma index.php` (or `php -S localhost:8080 index.php`) and open `/profile`. Saving answers `303 See Other` with a `flash` cookie; the page it redirects to shows

```html
<div class="wp-flashes" aria-live="polite"><div class="wp-flash wp-flash-success" role="status" data-timeout="6000"><span class="wp-flash-text">Saved, Ada.</span><form method="post" action="/flash/dismiss" class="wp-flash-dismiss" wp-dismiss><button type="submit" aria-label="Dismiss">&times;</button></form></div></div>
```

and clears the cookie. Reloading shows nothing: the message was shown once. After six seconds the script removes it; an empty name gives an error message instead, which stays until it is closed.

## What the plugin adds

| Part | What it does |
|---|---|
| [`Flash`](reference/Waypoint/Flash/Flash.md) service | `success()`, `info()`, `warning()`, `error()` queue a message; `#[Inject]` it anywhere |
| `$this->flashes()` view helper | renders the waiting messages and marks them as shown |
| middleware | reads the `flash` cookie before the request, writes or clears it afterwards |
| `POST /flash/dismiss` endpoint | closes the messages without JavaScript and returns to the page |
| `flash.css`, `flash.js` | styling per type, closing in place, auto-hide, focus on errors |
| [`FlashOptions`](reference/Waypoint/Flash/FlashOptions.md) | cookie name, lifetime and `Secure` flag, endpoint path, auto-hide delay, button label |

## How these docs are organised

- **[Guides](guides/index.md)**: [queueing messages](guides/messages.md), [rendering and the client script](guides/rendering.md), [configuration and the cookie](guides/configuration.md), [testing](guides/testing.md).
- **[Reference](reference/index.md)**: one page per class, generated from the code with `tenma docs`.
