---
title: "Flash"
description: "The message queue: queue a message now, show it on the next rendered page."
order: 100
generated: true
---

`Waypoint\Flash\Flash` · final class · namespace [`Waypoint\Flash`](index.md)

```php
final class Flash
```

The message queue: queue a message now, show it on the next rendered page.

```php
#[Inject]
private Flash $flash;

$this->flash->success('Profile updated.');
return new Redirect('/profile');
```

Messages travel in a cookie (FlashMiddleware reads and writes it), so no
session is needed. A message queued and rendered in the same request is
shown immediately. pull() hands the messages out exactly once.

## Constants

### TYPES

```php
public const TYPES = ['success', 'info', 'warning', 'error'];
```

The message types; each is also a CSS class suffix (`wp-flash-success`).

## Methods

### success()

```php
public function success(string $text): void
```

Queues a success message.

**Parameters**

- `$text` `string` — Plain text; it is escaped when rendered.

### info()

```php
public function info(string $text): void
```

Queues an informational message.

**Parameters**

- `$text` `string` — Plain text; it is escaped when rendered.

### warning()

```php
public function warning(string $text): void
```

Queues a warning.

**Parameters**

- `$text` `string` — Plain text; it is escaped when rendered.

### error()

```php
public function error(string $text): void
```

Queues an error: rendered with `role="alert"`, never hidden automatically, focused by the client script.

**Parameters**

- `$text` `string` — Plain text; it is escaped when rendered.

### add()

```php
public function add(string $type, string $text): void
```

Queues a message of the given type; a type not in TYPES becomes 'info'.

**Parameters**

- `$type` `string` — One of TYPES.
- `$text` `string` — Plain text; it is escaped when rendered.

### load()

```php
public function load(array $messages): void
```

Replaces the state with the messages that arrived with a request and drops anything queued.

FlashMiddleware calls it at the start and end of every request; application code does not need it.

**Parameters**

- `$messages` `list<array{type: string, text: string}>`

### pull()

```php
public function pull(): array
```

Everything waiting to be shown, handed out once; later calls in the same request return [].

`$this->flashes()` in the layout calls it. Pulled messages are not carried to the next request.

**Returns** `list<array{type: string, text: string}>`

### pending()

```php
public function pending(): array
```

The messages queued or received but not yet pulled: what the cookie must carry to the next request.

**Returns** `list<array{type: string, text: string}>`

### wasPulled()

```php
public function wasPulled(): bool
```

True once pull() handed something out in this request (the cookie can be cleared).

**Returns** `bool`
