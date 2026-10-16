---
title: "FlashOptions"
description: "Settings of the flash plugin: the cookie, the dismiss endpoint and the client behaviour."
order: 100
generated: true
---

`Waypoint\Flash\FlashOptions` · class · namespace [`Waypoint\Flash`](index.md)

```php
class FlashOptions
```

Settings of the flash plugin: the cookie, the dismiss endpoint and the client behaviour.

```php
$app->configure(function (FlashOptions $flash) {
    $flash->timeout = 4000;
    $flash->dismissLabel = 'Close';
});
```

## Properties

### $cookieName

```php
public string $cookieName = 'flash';
```

Cookie carrying queued messages from one request to the next.

### $ttl

```php
public int $ttl = 120;
```

Seconds a queued message survives unread (a redirect that never renders).

### $cookieSecure

```php
public bool $cookieSecure = true;
```

Cookie Secure attribute; false only for HTTP-only local development.

### $dismissPath

```php
public string $dismissPath = '/flash/dismiss';
```

Path of the no-JS dismiss endpoint (POST).

### $timeout

```php
public int $timeout = 6000;
```

Milliseconds after which the client hides a message; 0 keeps it. Errors are never auto-hidden.

### $dismissLabel

```php
public string $dismissLabel = 'Dismiss';
```

Accessible label of the dismiss button.
