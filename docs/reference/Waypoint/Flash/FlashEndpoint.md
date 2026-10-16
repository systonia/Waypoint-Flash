---
title: "FlashEndpoint"
description: "POST {dismissPath}: the no-JavaScript dismiss."
order: 100
generated: true
---

`Waypoint\Flash\FlashEndpoint` · final class · namespace [`Waypoint\Flash`](index.md)

```php
final class FlashEndpoint implements \Waypoint\Plugin\Endpoint
```

Implements `Waypoint\Plugin\Endpoint`

POST {dismissPath}: the no-JavaScript dismiss. It drops every pending message and goes back to the referring page.

## Methods

### __construct()

```php
public function __construct(
    private \Waypoint\Flash\Flash $flash,
    private \Waypoint\Flash\FlashOptions $options,
)
```

**Parameters**

- `$flash` [`Flash`](Flash.md)
- `$options` [`FlashOptions`](FlashOptions.md)

### serve()

```php
public function serve(
    string $method,
    string $path,
    \Waypoint\Http\Request $req,
    \Waypoint\Http\Response $res,
): bool
```

True if this endpoint handled the request and wrote the response.

**Parameters**

- `$method` `string`
- `$path` `string`
- `$req` `\Waypoint\Http\Request`
- `$res` `\Waypoint\Http\Response`

**Returns** `bool`
