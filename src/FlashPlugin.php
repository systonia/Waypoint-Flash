<?php

namespace Waypoint\Flash;

use Waypoint\Container;
use Waypoint\Plugin\{ClientAsset, PluginBase, ViewHelper};

/**
 * Register with `$app->plugin(new FlashPlugin())`. Adds the `Flash` service
 * (#[Inject]-able), `$this->flashes()` for the layout, the no-JS dismiss
 * endpoint, and the client script/CSS.
 */
final class FlashPlugin extends PluginBase implements ViewHelper, ClientAsset
{
    private Flash $flash;
    private FlashOptions $options;
    private FlashRenderer $renderer;
    private FlashCookie $cookie;

    public function name(): string
    {
        return 'flash';
    }

    /** Creates the Flash service and registers it in the container. */
    public function boot(Container $container, FlashOptions $options): void
    {
        $this->options = $options;
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

    /**
     * `flashes`: the rendered message region, see FlashRenderer::region().
     *
     * @return array<string, callable>
     */
    public function helpers(): array
    {
        return ['flashes' => fn(): string => $this->renderer->region($this->flash->pull())];
    }

    public function assets(): array
    {
        return ['flash.js' => __DIR__ . '/../assets/flash.js', 'flash.css' => __DIR__ . '/../assets/flash.css'];
    }
}
