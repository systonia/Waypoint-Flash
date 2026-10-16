<?php

namespace Waypoint\Flash;

/**
 * The message queue: queue a message now, show it on the next rendered page.
 *
 * ```php
 * #[Inject]
 * private Flash $flash;
 *
 * $this->flash->success('Profile updated.');
 * return new Redirect('/profile');
 * ```
 *
 * Messages travel in a cookie (FlashMiddleware reads and writes it), so no
 * session is needed. A message queued and rendered in the same request is
 * shown immediately. pull() hands the messages out exactly once.
 */
final class Flash
{
    /** The message types; each is also a CSS class suffix (`wp-flash-success`). */
    public const TYPES = ['success', 'info', 'warning', 'error'];

    /** @var list<array{type: string, text: string}> Arrived with this request's cookie. */
    private array $incoming = [];

    /** @var list<array{type: string, text: string}> Queued during this request. */
    private array $queued = [];

    private bool $pulled = false;

    /**
     * Queues a success message.
     *
     * @param string $text Plain text; it is escaped when rendered.
     */
    public function success(string $text): void
    {
        $this->add('success', $text);
    }

    /**
     * Queues an informational message.
     *
     * @param string $text Plain text; it is escaped when rendered.
     */
    public function info(string $text): void
    {
        $this->add('info', $text);
    }

    /**
     * Queues a warning.
     *
     * @param string $text Plain text; it is escaped when rendered.
     */
    public function warning(string $text): void
    {
        $this->add('warning', $text);
    }

    /**
     * Queues an error: rendered with `role="alert"`, never hidden automatically, focused by the client script.
     *
     * @param string $text Plain text; it is escaped when rendered.
     */
    public function error(string $text): void
    {
        $this->add('error', $text);
    }

    /**
     * Queues a message of the given type; a type not in TYPES becomes 'info'.
     *
     * @param string $type One of TYPES.
     * @param string $text Plain text; it is escaped when rendered.
     */
    public function add(string $type, string $text): void
    {
        $this->queued[] = ['type' => in_array($type, self::TYPES, true) ? $type : 'info', 'text' => $text];
    }

    /**
     * Replaces the state with the messages that arrived with a request and drops anything queued.
     *
     * FlashMiddleware calls it at the start and end of every request; application code does not need it.
     *
     * @param list<array{type: string, text: string}> $messages
     */
    public function load(array $messages): void
    {
        $this->incoming = $messages;
        $this->queued = [];
        $this->pulled = false;
    }

    /**
     * Everything waiting to be shown, handed out once; later calls in the same request return [].
     *
     * `$this->flashes()` in the layout calls it. Pulled messages are not carried to the next request.
     *
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

    /**
     * The messages queued or received but not yet pulled: what the cookie must carry to the next request.
     *
     * @return list<array{type: string, text: string}>
     */
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
