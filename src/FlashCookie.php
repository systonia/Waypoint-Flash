<?php

namespace Waypoint\Flash;

use Waypoint\Http\Response;
use Waypoint\Options\CsrfOptions;
use Waypoint\Support\{Arr, Base64Url};
use Waypoint\Waypoint;

/**
 * Encodes messages into the flash cookie and back: base64url(JSON), signed with
 * CsrfOptions::$secret when one is configured so a forged cookie is ignored.
 */
final class FlashCookie
{
    public function __construct(private FlashOptions $options)
    {
    }

    /**
     * Decodes a cookie value; an empty, malformed or wrongly signed value gives no messages.
     *
     * Unknown types become 'info'; entries without a string type and text are dropped.
     *
     * @param string|null $raw The cookie as the request sent it.
     * @return list<array{type: string, text: string}>
     */
    public function read(?string $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }
        [$payload, $signature] = explode('.', $raw, 2) + [1 => ''];
        $secret = $this->secret();
        if ($secret !== null && !hash_equals(Base64Url::sign($payload, $secret), $signature)) {
            return [];
        }
        $messages = [];
        foreach (Arr::listOfStringKeyed(json_decode(Base64Url::decode($payload), true)) as $item) {
            $type = $item['type'] ?? null;
            $text = $item['text'] ?? null;
            if (is_string($type) && is_string($text)) {
                $messages[] = ['type' => in_array($type, Flash::TYPES, true) ? $type : 'info', 'text' => $text];
            }
        }
        return $messages;
    }

    /**
     * Sets the cookie on the response, with FlashOptions::$ttl as Max-Age and FlashOptions::$cookieSecure.
     *
     * @param list<array{type: string, text: string}> $messages
     */
    public function write(Response $res, array $messages): void
    {
        $payload = Base64Url::encodeJson($messages);
        $secret = $this->secret();
        $value = $secret !== null ? $payload . '.' . Base64Url::sign($payload, $secret) : $payload;
        $res->withCookie($this->options->cookieName, $value, maxAge: $this->options->ttl, secure: $this->options->cookieSecure);
    }

    /** Expires the cookie on the response. */
    public function clear(Response $res): void
    {
        $res->withoutCookie($this->options->cookieName);
    }

    private function secret(): ?string
    {
        $csrf = Waypoint::getConfig(CsrfOptions::class);
        return isset($csrf->secret) ? $csrf->secret : null;
    }
}
