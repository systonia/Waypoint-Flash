<?php

namespace Waypoint\Flash;

use Waypoint\Http\{Redirect, Request, Response};
use Waypoint\Plugin\Endpoint;

/** POST {dismissPath}: the no-JavaScript dismiss -- drops every pending message and goes back to the referring page. */
final class FlashEndpoint implements Endpoint
{
    public function __construct(private Flash $flash, private FlashOptions $options)
    {
    }

    public function serve(string $method, string $path, Request $req, Response $res): bool
    {
        if ($method !== 'POST' || $path !== $this->options->dismissPath) {
            return false;
        }
        $this->flash->pull(); // consumed: the middleware clears the cookie
        $res->status(303)->withHeader('Location', self::backTo($req));
        return true;
    }

    /** The Referer's own path when it is same-origin, else '/'. */
    private static function backTo(Request $req): string
    {
        $referer = $req->headers['Referer'] ?? '';
        $parts = parse_url($referer);
        if (!is_array($parts) || !isset($parts['path'])) {
            return '/';
        }
        $host = $_SERVER['HTTP_HOST'] ?? null;
        if (isset($parts['host']) && (!is_string($host) || strcasecmp($parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : ''), $host) !== 0)) {
            return '/';
        }
        return $parts['path'] . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }
}
