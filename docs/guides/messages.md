---
title: Queueing messages
description: The Flash service, the four message types, and when a message is shown or kept.
order: 10
---

[`Flash`](../reference/Waypoint/Flash/Flash.md) is a service: the plugin registers one instance in the container, and any controller, middleware or service gets it with `#[Inject]`.

```php
use Waypoint\Attributes\{Controller, Inject, Post};
use Waypoint\Flash\Flash;
use Waypoint\Http\Redirect;

#[Controller('/orders')]
final class OrdersController
{
    #[Inject]
    private Flash $flash;

    #[Inject]
    private OrderService $orders;

    #[Post('/{id}/cancel')]
    public function cancel(int $id): Redirect
    {
        if (!$this->orders->cancel($id)) {
            $this->flash->warning("Order $id has already shipped and cannot be cancelled.");
            return new Redirect("/orders/$id", 303);
        }
        $this->flash->success("Order $id was cancelled.");
        return new Redirect('/orders', 303);
    }
}
```

## Types

| Method | Type | Rendered as |
|---|---|---|
| `success($text)` | `success` | `role="status"`, hides after `FlashOptions::$timeout` |
| `info($text)` | `info` | `role="status"`, hides after `FlashOptions::$timeout` |
| `warning($text)` | `warning` | `role="status"`, hides after `FlashOptions::$timeout` |
| `error($text)` | `error` | `role="alert"`, never hidden automatically, focused on page load |

`add($type, $text)` takes the type as a string, for code that decides it at run time; a type that is not one of `Flash::TYPES` becomes `info`:

```php
$this->flash->add($result->ok ? 'success' : 'error', $result->message);
```

Several messages can be queued in one request; they are shown in the order they were queued, after any that were still waiting from earlier requests.

## Text, not HTML

The text is escaped when it is rendered, so user input is safe in a message and markup shows as text:

```php
$this->flash->success('Saved <b>ok</b>');   // shows: Saved <b>ok</b>
```

A message carries no links or formatting. Keep messages short: the queue travels in a cookie, and browsers accept about 4 KB per cookie.

## When a message is shown

A message is shown by the next response that renders a layout calling `$this->flashes()`:

- **Queue, then redirect.** The redirect carries the message in the `flash` cookie; the page it leads to shows it and clears the cookie.
- **Queue, then render.** A message queued in a request that renders a page itself is shown on that very page; no cookie is written.

  ```php
  #[Get('/import')]
  public function import(): View
  {
      $count = $this->importer->run();
      $this->flash->info("$count rows imported.");
      return new View('Import');   // the layout shows the message right away
  }
  ```

- **Responses that render nothing keep the message.** A JSON response, a file download or another redirect does not call `$this->flashes()`, so the message stays in the cookie, which is sent again with a fresh lifetime. Messages queued in that request are added to it. The message appears on the next HTML page.

A message that no page picks up within [`FlashOptions::$ttl`](configuration.md#options) seconds (120 by default) expires with its cookie.

## Reading the queue yourself

`$this->flashes()` covers layouts. To show the messages some other way, for example in a JSON response for a script, take them with `pull()`:

```php
#[Get('/api/notices')]
public function notices(): array
{
    return $this->flash->pull();   // [['type' => 'success', 'text' => 'Saved, Ada.']]
}
```

`pull()` hands every waiting message out once: a second call in the same request returns `[]`, and the middleware clears the cookie afterwards. `pending()` returns the same list without consuming it.
