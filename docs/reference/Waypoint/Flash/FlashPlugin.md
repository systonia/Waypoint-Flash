---
title: "FlashPlugin"
description: "Register with `$app->plugin(new FlashPlugin())`."
order: 100
generated: true
---

`Waypoint\Flash\FlashPlugin` · final class · namespace [`Waypoint\Flash`](index.md)

```php
final class FlashPlugin extends \Waypoint\Plugin\PluginBase implements \Waypoint\Plugin\ClientAsset, \Waypoint\Plugin\ViewHelper
```

Extends `Waypoint\Plugin\PluginBase`  
Implements `Waypoint\Plugin\ClientAsset`, `Waypoint\Plugin\ViewHelper`

Register with `$app->plugin(new FlashPlugin())`. Adds the `Flash` service
(#[Inject]-able), `$this->flashes()` for the layout, the no-JS dismiss
endpoint, and the client script/CSS.

## Methods

### name()

```php
public function name(): string
```

Short unique name ('i18n'): the plan namespace for its route data, and the prefix in logs.

**Returns** `string`

### boot()

```php
public function boot(
    \Waypoint\Container $container,
    \Waypoint\Flash\FlashOptions $options,
    \Waypoint\Options\CsrfOptions $csrf,
): void
```

Creates the Flash service and registers it in the container; CsrfOptions::$secret signs the cookie.

**Parameters**

- `$container` `\Waypoint\Container`
- `$options` [`FlashOptions`](FlashOptions.md)
- `$csrf` `\Waypoint\Options\CsrfOptions`

### middlewares()

```php
public function middlewares(): array
```

**Returns** `list<callable>`: App-level middlewares, added at attach() in registration order.

### hooks()

```php
public function hooks(): array
```

**Returns** `list<object>`: Instances of Endpoint, Fallback, RouteAttributeCompiler, Guard, ResponseHook, ArgumentBinder, Renderer, OpenApiExtension, ViewHelper, ClientAsset.

### helpers()

```php
public function helpers(): array
```

`flashes`: the rendered message region, see FlashRenderer::region().

**Returns** `array<string, callable>`

### assets()

```php
public function assets(): array
```

**Returns** `array<string, string>`: {logical filename ending in .css or .js => absolute source path}
