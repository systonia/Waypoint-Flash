<?php

namespace Waypoint\Flash\Tests;

use Fiber;
use PHPUnit\Framework\TestCase;
use Waypoint\Flash\Flash;

final class FlashTest extends TestCase
{
    public function testMessagesAreKeptPerRequestWhenRequestsInterleave(): void
    {
        $flash = new Flash();
        $seen = [];
        // Two requests in Fibers of one worker, as under Tenma: each queues a message, waits, then reads
        $request = function (string $text) use ($flash, &$seen): Fiber {
            return new Fiber(function () use ($flash, $text, &$seen): void {
                $flash->load([]);                        // FlashMiddleware::before()
                $flash->success($text);
                Fiber::suspend();
                $seen[$text] = array_column($flash->pending(), 'text');
                $flash->load([]);                        // FlashMiddleware::after()
            });
        };

        $a = $request('A');
        $b = $request('B');
        $a->start();
        $b->start();
        $b->resume();
        $a->resume();

        $this->assertSame(['B' => ['B'], 'A' => ['A']], $seen);
        $this->assertSame([], $flash->pending());
    }
}
