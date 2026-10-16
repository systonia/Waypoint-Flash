<?php

namespace Waypoint\Flash;

/**
 * Settings of the flash plugin: the cookie, the dismiss endpoint and the client behaviour.
 *
 * ```php
 * $app->configure(function (FlashOptions $flash) {
 *     $flash->timeout = 4000;
 *     $flash->dismissLabel = 'Close';
 * });
 * ```
 */
class FlashOptions
{
    /** Cookie carrying queued messages from one request to the next. */
    public string $cookieName = 'flash';

    /** Seconds a queued message survives unread (a redirect that never renders). */
    public int $ttl = 120;

    /** Cookie Secure attribute. Null (default): Secure when the request came over HTTPS. */
    public ?bool $cookieSecure = null;

    /** Path of the no-JS dismiss endpoint (POST). */
    public string $dismissPath = '/flash/dismiss';

    /** Milliseconds after which the client hides a message; 0 keeps it. Errors are never auto-hidden. */
    public int $timeout = 6000;

    /** Accessible label of the dismiss button. */
    public string $dismissLabel = 'Dismiss';
}
