<?php

namespace Waypoint\Flash;

use Waypoint\Http\{MiddlewareBase, Request, Response};

/** Loads the cookie's messages into Flash before dispatch; afterwards writes what is still pending, or clears the cookie once shown. */
final class FlashMiddleware extends MiddlewareBase
{
    public function __construct(private Flash $flash, private FlashCookie $cookie, private FlashOptions $options)
    {
    }

    protected function before(Request $req, Response $res): bool
    {
        $raw = $_COOKIE[$this->options->cookieName] ?? null;
        $this->flash->load($this->cookie->read(is_string($raw) ? $raw : null));
        return true;
    }

    protected function after(Request $req, Response $res): void
    {
        $pending = $this->flash->pending();
        if ($pending !== []) {
            $this->cookie->write($res, $pending);
        } elseif ($this->flash->wasPulled()) {
            $this->cookie->clear($res);
        }
        $this->flash->load([]);
    }
}
