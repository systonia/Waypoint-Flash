---
title: "FlashCookie"
description: "Encodes messages into the flash cookie and back: base64url(JSON), signed with CsrfOptions::$secret when one is configured so a forged cookie is ignored."
order: 100
generated: true
---

`Waypoint\Flash\FlashCookie` · final class · namespace [`Waypoint\Flash`](index.md)

```php
final class FlashCookie
```

Encodes messages into the flash cookie and back: base64url(JSON), signed with
CsrfOptions::$secret when one is configured so a forged cookie is ignored.

## Methods

### __construct()

```php
public function __construct(
    private \Waypoint\Flash\FlashOptions $options,
    private \Waypoint\Options\CsrfOptions $csrf,
)
```

**Parameters**

- `$options` [`FlashOptions`](FlashOptions.md)
- `$csrf` `\Waypoint\Options\CsrfOptions`

### read()

```php
public function read(?string $raw): array
```

Decodes a cookie value; an empty, malformed or wrongly signed value gives no messages.

Unknown types become 'info'; entries without a string type and text are dropped.

**Parameters**

- `$raw` `string|null`: The cookie as the request sent it.

**Returns** `list<array{type: string, text: string}>`

### write()

```php
public function write(\Waypoint\Http\Response $res, array $messages): void
```

Sets the cookie on the response, with FlashOptions::$ttl as Max-Age and FlashOptions::$cookieSecure.

**Parameters**

- `$res` `\Waypoint\Http\Response`
- `$messages` `list<array{type: string, text: string}>`

### clear()

```php
public function clear(\Waypoint\Http\Response $res): void
```

Expires the cookie on the response.

**Parameters**

- `$res` `\Waypoint\Http\Response`
