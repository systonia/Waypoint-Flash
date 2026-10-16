---
title: "FlashRenderer"
description: "The HTML for the messages: one region, one block per message, each with a real dismiss form that works without JavaScript."
order: 100
generated: true
---

`Waypoint\Flash\FlashRenderer` · final class · namespace [`Waypoint\Flash`](index.md)

```php
final class FlashRenderer
```

The HTML for the messages: one region, one block per message, each with a real dismiss form that works without JavaScript.

## Methods

### __construct()

```php
public function __construct(private \Waypoint\Flash\FlashOptions $options)
```

**Parameters**

- `$options` [`FlashOptions`](FlashOptions.md)

### region()

```php
public function region(array $messages): string
```

The live region the layout renders: `<div class="wp-flashes" aria-live="polite">` with the messages inside.

**Parameters**

- `$messages` `list<array{type: string, text: string}>`

**Returns** `string`
