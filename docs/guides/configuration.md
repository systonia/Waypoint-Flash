---
title: Configuration and the cookie
description: FlashOptions, how messages travel in the cookie, and signing with the CSRF secret.
order: 30
---

## Options

[`FlashOptions`](../reference/Waypoint/Flash/FlashOptions.md) is set with `$app->configure()`, like every Options class:

```php
use Waypoint\Flash\FlashOptions;

$app->configure(function (FlashOptions $flash) {
    $flash->timeout = 4000;
    $flash->dismissLabel = 'Close';
});
```

| Option | Default | Meaning |
|---|---|---|
| `cookieName` | `'flash'` | name of the cookie that carries waiting messages |
| `ttl` | `120` | seconds a waiting message survives (the cookie's `Max-Age`) |
| `cookieSecure` | `null` | the cookie's `Secure` attribute; `null` sets it exactly when the request came over HTTPS (`Request::$https`) |
| `dismissPath` | `'/flash/dismiss'` | path of the no-JavaScript close endpoint (`POST`) |
| `timeout` | `6000` | milliseconds until the client script hides a message; `0` keeps every message. Errors are never hidden |
| `dismissLabel` | `'Dismiss'` | `aria-label` of the close button, for screen readers |

With `cookieSecure = true` a browser keeps the cookie to HTTPS, so on a site served over plain HTTP messages that need a redirect never arrive. Messages shown in the same request still work. Behind a proxy that ends TLS, the request counts as HTTPS only when Waypoint trusts that proxy, so set `true` there if it does not.

`dismissLabel` is the only text the plugin itself puts on the page. An app with several languages sets it per deployment, or translates it in a layout of its own.

Change `dismissPath` when the default collides with a route of the app; the endpoint is checked before routing and would otherwise answer `POST /flash/dismiss` itself.

## The cookie

The middleware reads the cookie before the request is handled and, afterwards:

- writes it again when messages are still waiting (queued in this request, or arrived and not shown), with a fresh `Max-Age` of `ttl`;
- clears it (`Max-Age=0`) when the waiting messages were shown or dismissed;
- leaves it alone when there was nothing to do.

It is set with `Path=/`, `HttpOnly` and `SameSite=Lax`, so scripts cannot read it and it travels with normal navigation, including the redirect after a form post.

The value is the list of messages as JSON, encoded as base64url:

```text
flash=W3sidHlwZSI6InN1Y2Nlc3MiLCJ0ZXh0IjoiU2F2ZWQsIEFkYS4ifV0.1Wf5_Spww9oIQzmIokmy2CA6R06_U9grcPkEnPAQmtc
       └ [{"type":"success","text":"Saved, Ada."}]              └ HMAC-SHA256 signature
```

## Signing

When `CsrfOptions::$secret` is set, the value carries a signature after a `.`: base64url HMAC-SHA256 of the payload with that secret. A cookie with a missing or wrong signature is ignored, so nobody can plant a message that looks like it came from the app, for example by setting the cookie on a shared parent domain.

```php
use Waypoint\Environment;
use Waypoint\Options\CsrfOptions;

$app->configure(function (CsrfOptions $csrf, Environment $env) {
    $csrf->secret = (string) $env->get('CSRF_SECRET');   // at least 32 bytes, e.g. bin2hex(random_bytes(32))
});
```

The secret must be at least 32 bytes long; a shorter one makes every request that reads or writes a signed flash cookie fail with an internal error. Changing the secret makes waiting messages invalid; they are dropped, which for messages with a two-minute life is harmless.

Without a CSRF secret the cookie is not signed. Messages still work, and their text is still escaped when shown, but a visitor can write their own.

## What a cookie may contain

Reading is lenient: an empty or malformed value gives no messages, an entry without a string `type` and `text` is skipped, and an unknown type becomes `info`. A broken cookie never breaks a page.
