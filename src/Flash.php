<?php

namespace Waypoint\Flash;

use Waypoint\Support\FiberLocal;

/**
 * The message queue: queue a message now, show it on the next rendered page.
 *
 * ```php
 * #[Inject]
 * private Flash $flash;
 *
 * $this->flash->success("Thank you, {$order->customerName}! Your order is reserved.");
 * return new Redirect("/orders/{$order->code}", 303);
 * ```
 *
 * Messages travel in a cookie (FlashMiddleware reads and writes it), so no
 * session is needed. A message queued and rendered in the same request is
 * shown immediately. pull() hands the messages out exactly once.
 *
 * One shared service, but the messages belong to the request: they are kept in a FiberLocal, so
 * requests a worker runs interleaved never see each other's messages.
 */
final class Flash
{
    /** The message types; each is also a CSS class suffix (`wp-flash-success`). */
    public const TYPES = ['success', 'info', 'warning', 'error'];

    /** @var FiberLocal<array{incoming: list<array{type: string, text: string}>, queued: list<array{type: string, text: string}>, pulled: bool}> */
    private FiberLocal $state;

    public function __construct()
    {
        $this->state = new FiberLocal();
    }

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
        $state = $this->state();
        $state['queued'][] = ['type' => in_array($type, self::TYPES, true) ? $type : 'info', 'text' => $text];
        $this->state->set($state);
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
        $this->state->set(['incoming' => $messages, 'queued' => [], 'pulled' => false]);
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
        $messages = $this->pending();
        $this->state->set(['incoming' => [], 'queued' => [], 'pulled' => $this->wasPulled() || $messages !== []]);
        return $messages;
    }

    /**
     * The messages queued or received but not yet pulled: what the cookie must carry to the next request.
     *
     * @return list<array{type: string, text: string}>
     */
    public function pending(): array
    {
        $state = $this->state();
        return [...$state['incoming'], ...$state['queued']];
    }

    /** True once pull() handed something out in this request (the cookie can be cleared). */
    public function wasPulled(): bool
    {
        return $this->state()['pulled'];
    }

    /** @return array{incoming: list<array{type: string, text: string}>, queued: list<array{type: string, text: string}>, pulled: bool} */
    private function state(): array
    {
        return $this->state->get() ?? ['incoming' => [], 'queued' => [], 'pulled' => false];
    }
}
