<?php

namespace Waypoint\Flash;

/** Configure via App::configure(function (FlashOptions $opts) { ... }). */
class FlashOptions
{
    /** Cookie carrying queued messages from one request to the next. */
    public string $cookieName = 'flash';

    /** Seconds a queued message survives unread (a redirect that never renders). */
    public int $ttl = 120;

    /** Cookie Secure attribute; false only for HTTP-only local development. */
    public bool $cookieSecure = true;

    /** The wp-container key of the region `$this->flashes()` renders and partial responses target. */
    public string $container = 'flash';

    /** Path of the no-JS dismiss endpoint (POST). */
    public string $dismissPath = '/flash/dismiss';

    /** Milliseconds after which the client hides a message; 0 keeps it. Errors are never auto-hidden. */
    public int $timeout = 6000;

    /** Accessible label of the dismiss button. */
    public string $dismissLabel = 'Dismiss';
}
