---
title: Queueing messages
description: The Flash service, the four message types, and when a message is shown or kept.
order: 10
---

[`Flash`](../reference/Waypoint/Flash/Flash.md) is a service: the plugin registers one instance in the container, and any controller, middleware or service gets it with `#[Inject]`.

The bakery example app queues its messages in two controllers. After the checkout the customer is thanked and sent on to the order page (from `src/Controllers/ShopController.php`):

```php
use Waypoint\Attributes\{Controller, Get, Inject};
use Waypoint\Exceptions\NotFoundException;
use Waypoint\Flash\Flash;
use Waypoint\Http\Redirect;
use Waypoint\I18n\Translator;

#[Controller]
final class ShopController
{
    #[Inject]
    private Orders $orders;

    #[Inject]
    private Flash $flash;

    #[Inject]
    private Translator $translator;

    /** Where the checkout sends the browser: a message for the next page, then the order itself. */
    #[Get('/orders/{code}/thanks')]
    public function thanks(string $code): Redirect
    {
        $order = $this->orders->byCode($code) ?? throw new NotFoundException('No such order');
        $this->flash->success($this->translator->t('Thank you, {name}! Your order is reserved.', ['name' => $order->customerName]));
        return new Redirect("/orders/{$order->code}", 303);
    }
}
```

The back office welcomes the staff after signing in and confirms signing out (from `src/Controllers/SessionController.php`):

```php
JWT::signIn($res, $user);
$this->flash->success($this->translator->t('Welcome, {name}.', ['name' => $user['name']]));
return new Redirect('/backoffice', 303);
```

```php
#[Post('/logout')]
public function logout(Response $res): Redirect
{
    JWT::signOut($res);
    $this->flash->info($this->translator->t('You are signed out.'));
    return new Redirect('/backoffice/login', 303);
}
```

The plugin takes the text as it is; translating it, as the bakery does with Waypoint I18n, happens before the call.

## Types

| Method | Type | Rendered as |
|---|---|---|
| `success($text)` | `success` | `role="status"`, hides after `FlashOptions::$timeout` |
| `info($text)` | `info` | `role="status"`, hides after `FlashOptions::$timeout` |
| `warning($text)` | `warning` | `role="status"`, hides after `FlashOptions::$timeout` |
| `error($text)` | `error` | `role="alert"`, never hidden automatically, focused on page load |

`add($type, $text)` takes the type as a string, for code that decides it at run time; a type that is not one of `Flash::TYPES` becomes `info`:

```php
$this->flash->add($product->stock > 0 ? 'success' : 'warning', $text);
```

Several messages can be queued in one request; they are shown in the order they were queued, after any that were still waiting from earlier requests.

The queue belongs to the request. `Flash` is one shared service, but it keeps the messages in a `Waypoint\Support\FiberLocal`, so under Tenma, where one worker runs requests concurrently, a request that waits never sees or sends the messages of another.

## Text, not HTML

The text is escaped when it is rendered, so user input is safe in a message and markup shows as text:

```php
$this->flash->success("Thank you, {$order->customerName}!");   // a customer named "<b>Mia</b>" shows as: Thank you, <b>Mia</b>!
```

A message carries no links or formatting. Keep messages short: the queue travels in a cookie, and browsers accept about 4 KB per cookie.

## When a message is shown

A message is shown by the next response that renders a layout calling `$this->flashes()`:

- **Queue, then redirect.** The redirect carries the message in the `flash` cookie; the page it leads to shows it and clears the cookie.
- **Queue, then render.** A message queued in a request that renders a page itself is shown on that very page; no cookie is written.

  ```php
  #[Get('/stock')]
  public function stock(): View
  {
      $soldOut = array_filter($this->products->all(), fn (Product $p) => $p->stock === 0);
      if ($soldOut !== []) {
          $this->flash->warning(count($soldOut) . ' products are sold out for today.');
      }
      return new View('Backoffice/Stock', layout: '_Backoffice.php');   // the layout shows the message right away
  }
  ```

- **Responses that render nothing keep the message.** A JSON response, a file download or another redirect does not call `$this->flashes()`, so the message stays in the cookie, which is sent again with a fresh lifetime. Messages queued in that request are added to it. The message appears on the next HTML page.

A message that no page picks up within [`FlashOptions::$ttl`](configuration.md#options) seconds (120 by default) expires with its cookie.

## Reading the queue yourself

`$this->flashes()` covers layouts. To show the messages some other way, for example in a JSON response for a script, take them with `pull()`:

```php
#[Get('/v1/notices')]
public function notices(): array
{
    return $this->flash->pull();   // [['type' => 'success', 'text' => 'Welcome, Ben.']]
}
```

`pull()` hands every waiting message out once: a second call in the same request returns `[]`, and the middleware clears the cookie afterwards. `pending()` returns the same list without consuming it.
