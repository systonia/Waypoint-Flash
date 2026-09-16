<?php

namespace Waypoint\Flash;

use Waypoint\Container;
use Waypoint\Http\{Request, Response};
use Waypoint\Plugin\{ClientAsset, PluginBase, ResponseHook, ViewHelper};

/**
 * Register with `$app->plugin(new FlashPlugin())`. Adds the `Flash` service
 * (#[Inject]-able), `$this->flashes()` for the layout, a partial-response
 * out-of-band fragment so waypoint.js navigations show messages too, the
 * no-JS dismiss endpoint, and the client script/CSS.
 */
final class FlashPlugin extends PluginBase implements ViewHelper, ResponseHook, ClientAsset
{
    private Flash $flash;
    private FlashOptions $options;
    private FlashRenderer $renderer;
    private FlashCookie $cookie;

    public function name(): string
    {
        return 'flash';
    }

    public function boot(Container $container): void
    {
        $this->options = $container->get(FlashOptions::class);
        $this->flash = new Flash();
        $this->renderer = new FlashRenderer($this->options);
        $this->cookie = new FlashCookie($this->options);
        $container->set($this->flash);
    }

    public function middlewares(): array
    {
        return [new FlashMiddleware($this->flash, $this->cookie, $this->options)];
    }

    public function hooks(): array
    {
        return [$this, new FlashEndpoint($this->flash, $this->options)];
    }

    public function helpers(): array
    {
        return ['flashes' => fn(): string => $this->renderer->region($this->flash->pull())];
    }

    /** A partial HTML page (a waypoint.js navigation) gets the messages appended as `<template wp-swap-oob>`; the layout isn't rendered there, so nothing else would show them. */
    public function after(array $plan, Request $req, Response $res): void
    {
        if (!$req->acceptPartial || $res->getStatus() !== 200 || !str_starts_with($res->getHeader('Content-Type') ?? '', 'text/html')) {
            return;
        }
        $messages = $this->flash->pull();
        $res->write(sprintf('<template wp-swap-oob="%s">%s</template>', htmlspecialchars($this->options->container, ENT_QUOTES), $this->renderer->messages($messages)));
    }

    public function assets(): array
    {
        return ['flash.js' => __DIR__ . '/../assets/flash.js', 'flash.css' => __DIR__ . '/../assets/flash.css'];
    }
}
