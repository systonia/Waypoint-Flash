---
title: "FlashMiddleware"
description: "Loads the cookie's messages into Flash before dispatch; afterwards writes what is still pending, or clears the cookie once shown."
order: 100
generated: true
---

`Waypoint\Flash\FlashMiddleware` · final class · namespace [`Waypoint\Flash`](index.md)

```php
final class FlashMiddleware extends \Waypoint\Http\MiddlewareBase
```

Extends `Waypoint\Http\MiddlewareBase`

Loads the cookie's messages into Flash before dispatch; afterwards writes what is still pending, or clears the cookie once shown.

## Methods

### __construct()

```php
public function __construct(
    private \Waypoint\Flash\Flash $flash,
    private \Waypoint\Flash\FlashCookie $cookie,
    private \Waypoint\Flash\FlashOptions $options,
)
```

**Parameters**

- `$flash` [`Flash`](Flash.md)
- `$cookie` [`FlashCookie`](FlashCookie.md)
- `$options` [`FlashOptions`](FlashOptions.md)
