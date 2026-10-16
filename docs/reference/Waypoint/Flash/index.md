---
title: "Waypoint\\Flash"
description: "The classes, interfaces, traits and enums in Waypoint\\Flash."
order: 200
generated: true
---

## Types

- [`Flash`](Flash.md) · final class: The message queue: queue a message now, show it on the next rendered page.
- [`FlashCookie`](FlashCookie.md) · final class: Encodes messages into the flash cookie and back: base64url(JSON), signed with CsrfOptions::$secret when one is configured so a forged cookie is ignored.
- [`FlashEndpoint`](FlashEndpoint.md) · final class: POST {dismissPath}: the no-JavaScript dismiss.
- [`FlashMiddleware`](FlashMiddleware.md) · final class: Loads the cookie's messages into Flash before dispatch; afterwards writes what is still pending, or clears the cookie once shown.
- [`FlashOptions`](FlashOptions.md) · class: Settings of the flash plugin: the cookie, the dismiss endpoint and the client behaviour.
- [`FlashPlugin`](FlashPlugin.md) · final class: Register with `$app->plugin(new FlashPlugin())`.
- [`FlashRenderer`](FlashRenderer.md) · final class: The HTML for the messages: one region, one block per message, each with a real dismiss form that works without JavaScript.
