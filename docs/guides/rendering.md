---
title: Rendering and the client script
description: The flashes() helper, the markup it writes, styling, auto-hide and dismissing with or without JavaScript.
order: 20
---

## The layout

```php
<body>
    <?= $this->flashes() ?>
    <main><?= $content ?></main>
    <?= $this->waypointJsTag() ?>
    <?= $this->pluginAssetTags() ?>
</body>
```

- `$this->flashes()` is a view helper the plugin registers. Waypoint views print `<?= ?>` as it is, so the markup arrives unchanged. The layout renders after the page's view, so it also shows messages the view queued.
- `$this->pluginAssetTags()` prints the `<script>` and `<link rel="stylesheet">` tags for `flash.js` and `flash.css`, served content-hashed under the assets path (`/assets/flash.flash.<hash>.js`).
- `flash.js` registers its behaviour with `Waypoint.directive()` from `waypoint.js`, so `$this->waypointJsTag()` must come before `pluginAssetTags()`.

Put `$this->flashes()` where messages belong on every page, usually above the main content. The helper always prints the region, empty when nothing waits, so the page layout does not jump.

## The markup

```html
<div class="wp-flashes" aria-live="polite">
  <div class="wp-flash wp-flash-success" role="status" data-timeout="6000">
    <span class="wp-flash-text">Saved, Ada.</span>
    <form method="post" action="/flash/dismiss" class="wp-flash-dismiss" wp-dismiss>
      <button type="submit" aria-label="Dismiss">&times;</button>
    </form>
  </div>
  <div class="wp-flash wp-flash-error" role="alert">
    <span class="wp-flash-text">Please enter your name.</span>
    <form method="post" action="/flash/dismiss" class="wp-flash-dismiss" wp-dismiss>
      <button type="submit" aria-label="Dismiss">&times;</button>
    </form>
  </div>
</div>
```

(The helper writes it on one line; it is indented here for reading.)

| Part | Meaning |
|---|---|
| `.wp-flashes`, `aria-live="polite"` | the region; screen readers announce messages that appear in it |
| `.wp-flash .wp-flash-<type>` | one message; `<type>` is `success`, `info`, `warning` or `error` |
| `role="status"` / `role="alert"` | `alert` for errors, `status` for the other types |
| `data-timeout` | milliseconds until the script removes the message; left out for errors and when `FlashOptions::$timeout` is `0` |
| `.wp-flash-text` | the escaped text |
| `.wp-flash-dismiss` form | the close button; `aria-label` is `FlashOptions::$dismissLabel` |

## Styling

`flash.css` gives each type a light background, a border and a text colour, and draws the close button as a plain `×`. `pluginAssetTags()` usually sits at the end of the body, after the app's own stylesheets, so a rule of the same specificity in the app loses to `flash.css`. Make the app's rules more specific, for example by prefixing them with the region class (or `body` for the region itself):

```css
body .wp-flashes { position: fixed; top: 16px; right: 16px; z-index: 10; width: min(360px, calc(100vw - 32px)); }
.wp-flashes .wp-flash { margin: 0 0 8px; box-shadow: 0 4px 12px rgb(0 0 0 / 0.12); }
.wp-flashes .wp-flash-success { background: #e8f5e9; border-color: #66bb6a; color: #1b5e20; }
```

## The client script

`flash.js` only adds to what already works without it:

- **Closing in place.** A click on the close button removes the message from the page instead of posting the form.
- **Auto-hide.** A message with `data-timeout` is removed after that many milliseconds. Errors have no `data-timeout` and stay.
- **Focus on errors.** On page load the first error in the region gets `tabindex="-1"` and keyboard focus, so a keyboard or screen reader user lands on it.

The script arms the timeouts and the focus once, when it loads. The close button works for messages added later too.

## Dismissing without JavaScript

Without the script, the close button submits its form: `POST /flash/dismiss` (the path is `FlashOptions::$dismissPath`). The endpoint drops every waiting message, clears the cookie and answers `303 See Other` back to the page:

- to the path and query of the `Referer` header when it points to the same host (`Host` header, port included): `http://app.test/page?x=1` returns to `/page?x=1`;
- to `/` in every other case: no `Referer`, one without a path, one that names another host, or a path starting with `//`, so the endpoint cannot be used to send visitors elsewhere.

The endpoint is a plugin endpoint, served before routing; the form carries no CSRF token and needs none, since it only removes the visitor's own messages.

Since the page that showed a message has already cleared the cookie, the endpoint usually has nothing left to drop. Its job is the round trip: the browser reloads the page, now without the message.
