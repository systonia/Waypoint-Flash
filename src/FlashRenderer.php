<?php

namespace Waypoint\Flash;

/** The HTML for the messages: one region, one block per message, each with a real dismiss form that works without JavaScript. */
final class FlashRenderer
{
    public function __construct(private FlashOptions $options)
    {
    }

    /**
     * The live region the layout renders: `<div class="wp-flashes" aria-live="polite">` with the messages inside.
     * @param list<array{type: string, text: string}> $messages
     */
    public function region(array $messages): string
    {
        return sprintf('<div class="wp-flashes" aria-live="polite">%s</div>', $this->messages($messages));
    }

    /**
     * One block per message, each with its dismiss form.
     * @param list<array{type: string, text: string}> $messages
     */
    private function messages(array $messages): string
    {
        $html = '';
        foreach ($messages as $message) {
            $isError = $message['type'] === 'error';
            $html .= sprintf(
                '<div class="wp-flash wp-flash-%1$s" role="%2$s"%3$s><span class="wp-flash-text">%4$s</span>'
                . '<form method="post" action="%5$s" class="wp-flash-dismiss" wp-dismiss><button type="submit" aria-label="%6$s">&times;</button></form></div>',
                $message['type'],
                $isError ? 'alert' : 'status',
                $isError || $this->options->timeout <= 0 ? '' : ' data-timeout="' . $this->options->timeout . '"',
                htmlspecialchars($message['text'], ENT_QUOTES),
                htmlspecialchars($this->options->dismissPath, ENT_QUOTES),
                htmlspecialchars($this->options->dismissLabel, ENT_QUOTES)
            );
        }
        return $html;
    }
}
