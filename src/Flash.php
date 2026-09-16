<?php

namespace Waypoint\Flash;

/**
 * Queue a message now, show it on the next rendered page:
 *
 *   $this->flash->success('Profile updated.');
 *   return new Redirect('/profile');
 *
 * Messages travel in a cookie (FlashMiddleware reads and writes it), so no
 * session is needed. A message queued and rendered in the same request is
 * shown immediately. pull() hands the messages out exactly once.
 */
final class Flash
{
    public const TYPES = ['success', 'info', 'warning', 'error'];

    /** @var list<array{type: string, text: string}> Arrived with this request's cookie. */
    private array $incoming = [];

    /** @var list<array{type: string, text: string}> Queued during this request. */
    private array $queued = [];

    private bool $pulled = false;

    public function success(string $text): void
    {
        $this->add('success', $text);
    }

    public function info(string $text): void
    {
        $this->add('info', $text);
    }

    public function warning(string $text): void
    {
        $this->add('warning', $text);
    }

    public function error(string $text): void
    {
        $this->add('error', $text);
    }

    public function add(string $type, string $text): void
    {
        $this->queued[] = ['type' => in_array($type, self::TYPES, true) ? $type : 'info', 'text' => $text];
    }

    /** @param list<array{type: string, text: string}> $messages */
    public function load(array $messages): void
    {
        $this->incoming = $messages;
        $this->queued = [];
        $this->pulled = false;
    }

    /**

     * Everything waiting to be shown, handed out once; later calls in the same request return [].

     * @return list<array{type: string, text: string}>

     */
    public function pull(): array
    {
        $messages = [...$this->incoming, ...$this->queued];
        $this->incoming = [];
        $this->queued = [];
        $this->pulled = $this->pulled || $messages !== [];
        return $messages;
    }

    /** @return list<array{type: string, text: string}> Queued but not yet rendered -- what the cookie must carry to the next request. */
    public function pending(): array
    {
        return [...$this->incoming, ...$this->queued];
    }

    /** True once pull() handed something out in this request (the cookie can be cleared). */
    public function wasPulled(): bool
    {
        return $this->pulled;
    }
}
