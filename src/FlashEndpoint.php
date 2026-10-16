<?php

namespace Waypoint\Flash;

use Waypoint\Http\{Redirect, Request, Response};
use Waypoint\Plugin\Endpoint;

/** POST {dismissPath}: the no-JavaScript dismiss. It drops every pending message and goes back to the referring page. */
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

    /** The Referer's own path and query when it names this host (`Host`, port included), else '/'. */
    private static function backTo(Request $req): string
    {
        $parts = parse_url($req->headers['Referer'] ?? '');
        $host = $req->headers['Host'] ?? null;
        if (!is_array($parts) || !isset($parts['host']) || !is_string($host)
            || strcasecmp($parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : ''), $host) !== 0
            || !preg_match('#^/(?![/\\\\])#', $parts['path'] ?? '')) { // '//x' or '/\x' would leave the host
            return '/';
        }
        return $parts['path'] . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }
}
